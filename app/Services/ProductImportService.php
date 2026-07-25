<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import produk massal dari Excel dengan pemetaan kolom bebas.
 * Kategori memakai taksonomi bertingkat (categories) — sub-kategori "Induk > Sub".
 * SKU cocok → update (upsert); baru → dibuat.
 */
class ProductImportService
{
    /** Definisi kolom yang bisa diimpor + alias untuk auto-mapping. */
    public const COLUMNS = [
        ['col' => 'name', 'label' => 'Nama Produk', 'required' => true, 'aliases' => ['name', 'nama', 'nama produk', 'product name', 'produk']],
        ['col' => 'sku', 'label' => 'SKU', 'aliases' => ['sku', 'kode', 'kode produk', 'code']],
        ['col' => 'brand', 'label' => 'Brand / Merek', 'aliases' => ['brand', 'merek', 'merk']],
        ['col' => 'category', 'label' => 'Kategori (Induk > Sub)', 'aliases' => ['kategori', 'category', 'categories', 'kategori produk']],
        ['col' => 'price', 'label' => 'Harga', 'required' => true, 'aliases' => ['harga', 'price', 'harga jual']],
        ['col' => 'compare_at_price', 'label' => 'Harga Coret', 'aliases' => ['harga coret', 'compare', 'compare at price', 'harga asli']],
        ['col' => 'stock', 'label' => 'Stok', 'aliases' => ['stok', 'stock', 'qty', 'kuantitas']],
        ['col' => 'weight_grams', 'label' => 'Berat (gram)', 'aliases' => ['berat', 'weight', 'berat gram', 'weight grams', 'berat (gram)']],
        ['col' => 'short_description', 'label' => 'Deskripsi Singkat', 'aliases' => ['deskripsi singkat', 'short description', 'ringkasan']],
        ['col' => 'description', 'label' => 'Deskripsi', 'aliases' => ['deskripsi', 'description', 'keterangan']],
        ['col' => 'shipping_method', 'label' => 'Metode Pengiriman (internal/courier)', 'aliases' => ['metode pengiriman', 'shipping method', 'pengiriman', 'shipping']],
        ['col' => 'internal_shipping_fee', 'label' => 'Ongkir Internal', 'aliases' => ['ongkir internal', 'ongkir', 'internal shipping fee', 'biaya kirim']],
        ['col' => 'can_be_bundled', 'label' => 'Bisa Dibundle (ya/tidak)', 'aliases' => ['bisa dibundle', 'can be bundled', 'bundle', 'bisa dibundel']],
        ['col' => 'service_scope', 'label' => 'Cakupan Layanan (all/specific)', 'aliases' => ['cakupan layanan', 'service scope', 'scope']],
        ['col' => 'is_active', 'label' => 'Aktif (ya/tidak)', 'aliases' => ['aktif', 'active', 'is active', 'status']],
        ['col' => 'is_featured', 'label' => 'Unggulan (ya/tidak)', 'aliases' => ['unggulan', 'featured', 'is featured']],
    ];

    private function norm(string $s): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $s) ?? '');
    }

    private function sheetRows(string $path): array
    {
        $data = Excel::toArray(new class implements ToArray {
            public function array(array $array) {}
        }, $path);

        return $data[0] ?? [];
    }

    /** Baca baris header (baris pertama). */
    public function readHeaders(string $path): array
    {
        $rows = $this->sheetRows($path);

        return collect($rows[0] ?? [])
            ->map(fn ($h) => trim((string) $h))
            ->filter(fn ($h) => $h !== '')
            ->values()
            ->all();
    }

    /** Saran pemetaan otomatis: [db_col => header|null]. 2 pass: exact lalu contains. */
    public function suggest(array $headers): array
    {
        $normHeaders = array_map(fn ($h) => $this->norm($h), $headers);
        $used = [];
        $map = [];

        // Pass 1: kecocokan persis (ternormalisasi).
        foreach (self::COLUMNS as $c) {
            $map[$c['col']] = null;
            foreach ($headers as $idx => $h) {
                if (in_array($h, $used, true)) continue;
                foreach ($c['aliases'] as $a) {
                    if ($this->norm($a) === $normHeaders[$idx]) {
                        $map[$c['col']] = $h;
                        $used[] = $h;
                        break 2;
                    }
                }
            }
        }

        // Pass 2: saling-mengandung untuk kolom yang belum terpetakan.
        foreach (self::COLUMNS as $c) {
            if ($map[$c['col']]) continue;
            foreach ($headers as $idx => $h) {
                if (in_array($h, $used, true)) continue;
                $nh = $normHeaders[$idx];
                foreach ($c['aliases'] as $a) {
                    $na = $this->norm($a);
                    if (strlen($na) >= 4 && ($nh !== '' && (str_contains($nh, $na) || str_contains($na, $nh)))) {
                        $map[$c['col']] = $h;
                        $used[] = $h;
                        break 2;
                    }
                }
            }
        }

        return $map;
    }

    private function toInt($v): int
    {
        if ($v === null || $v === '') return 0;
        return (int) round((float) preg_replace('/[^0-9.\-]/', '', (string) $v));
    }

    private function toBool($v, bool $default): bool
    {
        if ($v === null || $v === '') return $default;
        $s = strtolower(trim((string) $v));
        if (in_array($s, ['1', 'ya', 'yes', 'true', 'aktif', 'y', 'active'], true)) return true;
        if (in_array($s, ['0', 'tidak', 'no', 'false', 'nonaktif', 'n', 'inactive'], true)) return false;
        return $default;
    }

    /** Resolusi kategori taksonomi bertingkat dari "Induk > Sub"; auto-buat. */
    private function resolveCategory(?string $raw): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;

        $parts = collect(preg_split('/\s*>\s*/', $raw))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->values();
        if ($parts->isEmpty()) return null;

        $parentId = null;
        $category = null;
        foreach ($parts as $name) {
            $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->where('parent_id', $parentId)
                ->first()
                ?? Category::create([
                    'parent_id' => $parentId,
                    'name' => $name,
                    'slug' => $this->uniqueCategorySlug($name),
                    'icon' => 'inventory_2',
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
            $parentId = $category->id;
        }

        return $category?->id;
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    private function uniqueProductSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $i = 1;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    private function generateSku(string $name): string
    {
        return strtoupper(Str::slug(Str::limit($name, 6, ''))) . '-' . strtoupper(Str::random(4));
    }

    /**
     * Jalankan import. $mapping: [db_col => header_string].
     * Return ['created'=>int,'updated'=>int,'errors'=>[['row'=>n,'message'=>..]], 'total'=>int].
     */
    public function import(string $path, array $mapping): array
    {
        $rows = $this->sheetRows($path);
        $header = collect($rows[0] ?? [])->map(fn ($h) => trim((string) $h))->all();
        $dataRows = array_slice($rows, 1);

        $indexOf = function (?string $h) use ($header) {
            if (! $h) return false;
            return array_search($h, $header, true);
        };

        $created = 0;
        $updated = 0;
        $errors = [];
        $total = 0;

        foreach ($dataRows as $i => $row) {
            // Lewati baris yang benar-benar kosong.
            if (! collect($row)->contains(fn ($c) => trim((string) $c) !== '')) {
                continue;
            }
            $total++;
            $rowNo = $i + 2; // +1 header, +1 index-0

            $get = function (string $col) use ($mapping, $indexOf, $row) {
                $idx = $indexOf($mapping[$col] ?? null);
                if ($idx === false) return null;
                $v = $row[$idx] ?? null;
                return is_string($v) ? trim($v) : $v;
            };

            try {
                $name = $get('name');
                if ($name === null || $name === '') {
                    throw new \RuntimeException('Nama produk kosong.');
                }
                $priceRaw = $get('price');
                if ($priceRaw === null || $priceRaw === '') {
                    throw new \RuntimeException('Harga kosong.');
                }

                $shipping = strtolower((string) ($get('shipping_method') ?: 'courier'));
                $shipping = in_array($shipping, ['internal', 'courier'], true) ? $shipping : 'courier';
                $scope = strtolower((string) ($get('service_scope') ?: 'all'));
                $scope = in_array($scope, ['all', 'specific'], true) ? $scope : 'all';

                $data = [
                    'name' => $name,
                    'brand' => $get('brand') ?: null,
                    'category_id' => $this->resolveCategory($get('category')),
                    'product_category_id' => null,
                    'short_description' => $get('short_description') ?: null,
                    'description' => $get('description') ?: null,
                    'price' => $this->toInt($priceRaw),
                    'compare_at_price' => $get('compare_at_price') ? $this->toInt($get('compare_at_price')) : null,
                    'weight_grams' => $this->toInt($get('weight_grams')),
                    'stock' => $this->toInt($get('stock')),
                    'shipping_method' => $shipping,
                    'internal_shipping_fee' => $shipping === 'internal' ? ($get('internal_shipping_fee') ? $this->toInt($get('internal_shipping_fee')) : null) : null,
                    'can_be_bundled' => $this->toBool($get('can_be_bundled'), false),
                    'service_scope' => $scope,
                    'is_active' => $this->toBool($get('is_active'), true),
                    'is_featured' => $this->toBool($get('is_featured'), false),
                ];

                $sku = $get('sku');
                $existing = $sku ? Product::where('sku', $sku)->first() : null;

                if ($existing) {
                    $data['slug'] = $this->uniqueProductSlug($name, $existing->id);
                    $existing->update($data);
                    $updated++;
                } else {
                    $data['sku'] = $sku ?: $this->generateSku($name);
                    $data['slug'] = $this->uniqueProductSlug($name);
                    Product::create($data);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNo, 'message' => $e->getMessage()];
            }
        }

        return compact('created', 'updated', 'errors', 'total');
    }
}
