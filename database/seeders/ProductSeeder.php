<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MobileService;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $imgA = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDpqsNkAgbqbjx2Il29wqoCj4ows9By6RReC2pX0jS3-liIzBWD_jyRIrgJoZlNwijY6izYwSuqlprH4Y-3gJJ9zemAyjKyTGDXdMeUGZ5lPTEZRt9oLrLyLtt4X_nqLazinYUNZOlYyEK6Q-FetFDaSJZSCLzO3JoAyDd1j5F-hdTzOBfrzgIQ4-EAyVEeQSVzkRuSCBXA9g7gPDFTX-iLJP5Tr8Il0OkoaFPPSeQiXVcCFyRs36VkBEHoqZcWO9Mm5230eTuUrYI';
        $imgB = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAvs2NoIlUz-wIZDIuHHd_lz0FQTSwunHgrteixQrxuaA2tUXABlKInciOnr-eOSILVc3N2YF0yZyqKKBYerq0gNsuXuY4VxzwVFNh9PAaF_kL5ccwal4EcWl9LtjsJAFWCc8qsHcTaeUThHLc_QnmzW4WoLUSPJ_oCeCsLbqD6Q6ldoh_9xCVQKf4ajGc8sHUDhvhQVAhBww0KupnYEkuYIaAotylMZRK_kFp2ZLxDYZSQHYkgqvsO4jF4EjTm1huB3ScEC4QMbtw';

        $categories = collect(['Sofa', 'Meja', 'Kursi', 'Rak', 'Material'])->mapWithKeys(function ($name, $i) {
            $cat = ProductCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i, 'is_active' => true],
            );

            return [$name => $cat->id];
        });

        $serviceId = MobileService::query()->orderBy('id')->value('id');

        $products = [
            ['name' => 'HAKATA Sofa 3 Dudukan Ash Grey', 'cat' => 'Sofa', 'brand' => 'HAKATA', 'price' => 3499000, 'compare' => 3999000, 'weight' => 32000, 'stock' => 12, 'rating' => 4.9, 'sold' => 128, 'bundle' => true, 'scope' => 'all', 'fee' => 150000, 'featured' => true, 'img' => $imgA],
            ['name' => 'NARA Meja Makan Solid Wood', 'cat' => 'Meja', 'brand' => 'NARA', 'price' => 2150000, 'weight' => 40000, 'stock' => 8, 'rating' => 4.8, 'sold' => 54, 'bundle' => true, 'scope' => 'all', 'fee' => 180000, 'img' => $imgB],
            ['name' => 'IKARA Rak Buku 5 Tingkat Walnut', 'cat' => 'Rak', 'brand' => 'IKARA', 'price' => 1850000, 'compare' => 2100000, 'weight' => 25000, 'stock' => 15, 'rating' => 4.7, 'sold' => 96, 'bundle' => true, 'scope' => 'all', 'fee' => 120000, 'featured' => true, 'img' => $imgA],
            ['name' => 'LOME Kursi Kerja Ergonomis', 'cat' => 'Kursi', 'brand' => 'LOME', 'price' => 1290000, 'weight' => 14000, 'stock' => 20, 'rating' => 4.8, 'sold' => 71, 'bundle' => false, 'scope' => 'all', 'fee' => 90000, 'img' => $imgB],
            ['name' => 'Cat Tembok Premium 25kg Putih', 'cat' => 'Material', 'brand' => 'AVITEX', 'price' => 450000, 'weight' => 25000, 'stock' => 100, 'rating' => 4.9, 'sold' => 320, 'bundle' => true, 'scope' => 'specific', 'fee' => 50000, 'img' => $imgA],
            ['name' => 'Keramik Lantai 60x60 Motif Marmer', 'cat' => 'Material', 'brand' => 'ROMAN', 'price' => 92000, 'compare' => 110000, 'weight' => 20000, 'stock' => 500, 'rating' => 4.6, 'sold' => 210, 'bundle' => true, 'scope' => 'specific', 'fee' => 40000, 'img' => $imgB],
            ['name' => 'Semen Instan 40kg', 'cat' => 'Material', 'brand' => 'MU', 'price' => 78000, 'weight' => 40000, 'stock' => 300, 'rating' => 4.7, 'sold' => 180, 'bundle' => true, 'scope' => 'specific', 'fee' => 45000, 'img' => $imgB],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'sku' => strtoupper(Str::slug(Str::limit($data['name'], 8, ''))) . '-' . strtoupper(Str::substr(md5($data['name']), 0, 4)),
                    'product_category_id' => $categories[$data['cat']] ?? null,
                    'name' => $data['name'],
                    'brand' => $data['brand'],
                    'short_description' => $data['name'] . ' berkualitas untuk kebutuhan hunian & proyek.',
                    'description' => 'Produk pilihan dengan kualitas terjamin. ' . $data['name'] . '. Berat ' . ($data['weight'] / 1000) . ' kg.',
                    'price' => $data['price'],
                    'compare_at_price' => $data['compare'] ?? null,
                    'weight_grams' => $data['weight'],
                    'stock' => $data['stock'],
                    'rating' => $data['rating'],
                    'sold_count' => $data['sold'],
                    'primary_image' => $data['img'],
                    'can_be_bundled' => $data['bundle'],
                    'service_scope' => $data['scope'],
                    'shipping_method' => 'internal',
                    'internal_shipping_fee' => $data['fee'],
                    'is_active' => true,
                    'is_featured' => $data['featured'] ?? false,
                ],
            );

            if ($data['scope'] === 'specific' && $serviceId) {
                $product->services()->sync([$serviceId]);
            } else {
                $product->services()->detach();
            }
        }

        $this->seedInteriorItems($imgA, $imgB);

        $this->command?->info('Seeded ' . count($products) . ' products in ' . $categories->count() . ' categories.');
    }

    /**
     * Item furnitur "Fit Out Interior (Item)" — dulu keliru jadi layanan, kini
     * produk katalog yang memakai kategori master (category_id) yang sama dengan
     * layanan. Harga = contoh, admin bisa sesuaikan.
     */
    private function seedInteriorItems(string $imgA, string $imgB): void
    {
        $masterCategoryId = Category::where('slug', 'fit-out-interior-item')->value('id');

        // [nama, harga contoh, berat gram, stok]
        $items = [
            ['Lemari Pakaian', 4500000, 45000, 8, $imgA],
            ['Kabinet TV', 2800000, 28000, 10, $imgB],
            ['Peninsula', 6500000, 60000, 5, $imgA],
            ['Table Island', 5500000, 55000, 6, $imgB],
            ['Sofa', 3500000, 32000, 12, $imgA],
            ['Kabinet Penyimpanan', 3200000, 35000, 9, $imgB],
            ['Meja Kerja', 1800000, 18000, 15, $imgA],
            ['Meja Belajar', 1500000, 16000, 15, $imgB],
            ['Rak Penyimpanan', 2200000, 22000, 12, $imgA],
            ['Meja Makan', 2900000, 40000, 8, $imgB],
        ];

        foreach ($items as $order => [$name, $price, $weight, $stock, $img]) {
            Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'sku' => 'FOI-' . str_pad((string) ($order + 1), 3, '0', STR_PAD_LEFT),
                    'category_id' => $masterCategoryId,
                    'name' => $name,
                    'short_description' => $name . ' untuk kebutuhan fit out interior.',
                    'description' => $name . ' — produk furnitur custom. Harga contoh, silakan sesuaikan.',
                    'price' => $price,
                    'weight_grams' => $weight,
                    'stock' => $stock,
                    'primary_image' => $img,
                    'can_be_bundled' => true,
                    'service_scope' => 'all',
                    'shipping_method' => 'internal',
                    'is_active' => true,
                    'is_featured' => false,
                ],
            );
        }

        $this->command?->info('Seeded ' . count($items) . ' interior items (kategori Fit Out Interior (Item)).');
    }
}
