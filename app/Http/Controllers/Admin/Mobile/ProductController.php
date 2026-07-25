<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\ProductAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProductAdminService $service
    ) {
        $this->setView('admin.pages.mobile.products');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('product', function ($product) {
                    $img = $product->primary_image
                        ? '<img src="' . e(Storage::disk('public')->url($product->primary_image)) . '" style="width:38px;height:38px;object-fit:cover;border-radius:8px;">'
                        : '<span class="d-inline-flex align-items-center justify-content-center bg-light text-muted" style="width:38px;height:38px;border-radius:8px;"><i class="ri-image-line"></i></span>';

                    return '<div class="d-flex align-items-center" style="gap:10px">' . $img . '
                        <div class="d-flex flex-column"><span class="fw-semibold">' . e($product->name) . '</span><small class="text-muted">' . e($product->sku) . '</small></div></div>';
                })
                ->addColumn('category', function ($product) {
                    $cat = $product->masterCategory;
                    if (! $cat) {
                        return '<span class="text-muted">—</span>';
                    }
                    // Susun jalur penuh: Kategori › Sub › Sub 2 (dari akar ke daun).
                    $names = [];
                    $node = $cat;
                    $guard = 0;
                    while ($node && $guard < 6) {
                        array_unshift($names, $node->name);
                        $node = $node->parent;
                        $guard++;
                    }
                    $path = implode(' › ', $names);

                    // Clip dengan ellipsis; jalur penuh tampil sebagai tooltip.
                    return '<span title="' . e($path) . '" style="display:inline-block;max-width:230px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle;">' . e($path) . '</span>';
                })
                ->addColumn('price', function ($product) {
                    $price = 'Rp' . number_format($product->price, 0, ',', '.');
                    if ($product->compare_at_price) {
                        $price .= ' <small class="text-muted"><del>Rp' . number_format($product->compare_at_price, 0, ',', '.') . '</del></small>';
                    }

                    return $price;
                })
                ->addColumn('stock', fn ($product) => (int) $product->stock)
                ->addColumn('settings', function ($product) {
                    $badges = [];
                    if ($product->can_be_bundled) {
                        $badges[] = '<span class="badge badge-info badge-sm">Bundle</span>';
                    }
                    $badges[] = $product->service_scope === 'specific'
                        ? '<span class="badge badge-warning badge-sm">Layanan tertentu</span>'
                        : '<span class="badge badge-light badge-sm">Semua layanan</span>';
                    $badges[] = $product->shipping_method === 'courier'
                        ? '<span class="badge badge-secondary badge-sm">Jasa kurir</span>'
                        : '<span class="badge badge-success badge-sm">Kurir internal</span>';

                    return implode(' ', $badges);
                })
                ->addColumn('status', fn ($product) => $product->is_active
                    ? '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>'
                    : '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>')
                ->addColumn('action', function ($product) {
                    return '<div class="d-flex w-full justify-content-center align-items-center" style="gap:10px">
                        <a href="' . route('admin.mobile.products.edit', $product->id) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                        <a href="javascript:void(0)" onclick="deleteProduct(' . $product->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>';
                })
                ->rawColumns(['product', 'category', 'price', 'settings', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $product = null;

        try {
            if ($request->filled('id_product')) {
                $product = $this->service->find((int) $request->id_product);
            }

            $categories = $this->service->categories();
            $services = $this->service->services();
            $categoryTree = \App\Models\Category::orderBy('sort_order')->orderBy('name')->get(['id', 'parent_id', 'name']);

            return $this->setView('admin.components.forms.')->view($request->view, compact('product', 'categories', 'services', 'categoryTree'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /** Data pendukung form produk (kategori, layanan, pohon kategori). */
    private function formData(): array
    {
        return [
            'categories' => $this->service->categories(),
            'services' => $this->service->services(),
            'categoryTree' => \App\Models\Category::orderBy('sort_order')->orderBy('name')->get(['id', 'parent_id', 'name']),
        ];
    }

    /** Halaman tambah produk (bukan modal). */
    public function create()
    {
        return $this->view('create', array_merge(['product' => null], $this->formData()));
    }

    /** Halaman edit produk (bukan modal). */
    public function edit(int $id)
    {
        return $this->view('edit', array_merge(['product' => $this->service->find($id)], $this->formData()));
    }

    public function store(Request $request)
    {
        try {
            [$data, $serviceIds, $image] = $this->validatePayload($request);
            $this->service->create($data, $serviceIds, $image);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan produk.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            [$data, $serviceIds, $image] = $this->validatePayload($request, $id);
            $this->service->update($id, $data, $serviceIds, $image);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah produk.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_product);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus produk.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($id)],
            'name' => ['required', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'can_be_bundled' => ['required', 'boolean'],
            'service_scope' => ['required', 'in:all,specific'],
            'shipping_method' => ['required', 'in:internal,courier'],
            'internal_shipping_fee' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'primary_image' => ['nullable', 'image', 'max:4096'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer'],
        ]);

        $serviceIds = $validated['service_ids'] ?? [];
        $image = $request->file('primary_image');

        $data = collect($validated)->except(['service_ids', 'primary_image'])->toArray();
        $data['is_featured'] = (bool) ($validated['is_featured'] ?? false);
        $data['weight_grams'] = $validated['weight_grams'] ?? 0;
        $data['stock'] = $validated['stock'] ?? 0;
        $data['internal_shipping_fee'] = $validated['shipping_method'] === 'internal' ? ($validated['internal_shipping_fee'] ?? null) : null;

        return [$data, $serviceIds, $image];
    }

    // ============ Import produk dari Excel ============

    /** Unduh template Excel produk. */
    public function importTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductImportTemplateExport,
            'template-import-produk.xlsx'
        );
    }

    /** Upload Excel → simpan sementara, kembalikan header + saran pemetaan. */
    public function importUpload(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            ]);

            $file = $request->file('file');
            $token = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('local')->putFileAs('imports', $file, $token);
            $path = Storage::disk('local')->path('imports/' . $token);

            $service = app(\App\Services\ProductImportService::class);
            $headers = $service->readHeaders($path);

            if (empty($headers)) {
                Storage::disk('local')->delete('imports/' . $token);
                return response()->json(['message' => 'File tidak memiliki baris header.'], 422);
            }

            return response()->json([
                'token' => $token,
                'headers' => $headers,
                'columns' => \App\Services\ProductImportService::COLUMNS,
                'suggestion' => $service->suggest($headers),
            ]);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'File tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal membaca file: ' . $th->getMessage()], 500);
        }
    }

    /** Eksekusi import dengan pemetaan kolom. */
    public function importExecute(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => ['required', 'string'],
                'mapping' => ['required', 'array'],
            ]);

            $token = basename($validated['token']);
            $relative = 'imports/' . $token;
            if (! Storage::disk('local')->exists($relative)) {
                return response()->json(['message' => 'Sesi upload kedaluwarsa. Silakan unggah ulang file.'], 422);
            }

            // Buang pemetaan kosong.
            $mapping = collect($validated['mapping'])->filter(fn ($v) => is_string($v) && $v !== '')->all();
            if (empty($mapping['name'] ?? null) || empty($mapping['price'] ?? null)) {
                return response()->json(['message' => 'Kolom Nama Produk dan Harga wajib dipetakan.'], 422);
            }

            $result = app(\App\Services\ProductImportService::class)
                ->import(Storage::disk('local')->path($relative), $mapping);

            Storage::disk('local')->delete($relative);

            return response()->json([
                'message' => "Import selesai: {$result['created']} baru, {$result['updated']} diperbarui, " . count($result['errors']) . ' gagal.',
                'result' => $result,
            ]);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Gagal import: ' . $th->getMessage()], 500);
        }
    }
}
