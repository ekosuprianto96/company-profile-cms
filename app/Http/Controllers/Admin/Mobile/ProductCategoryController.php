<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\ProductCategoryAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProductCategoryAdminService $service
    ) {
        $this->setView('admin.pages.mobile.product-categories');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('category', fn ($c) => '<span class="fw-semibold">' . e($c->name) . '</span><br><small class="text-muted">' . e($c->slug) . '</small>')
                ->addColumn('products_count', fn ($c) => (int) $c->products_count)
                ->addColumn('sort_order', fn ($c) => (int) $c->sort_order)
                ->addColumn('status', fn ($c) => $c->is_active
                    ? '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>'
                    : '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>')
                ->addColumn('action', fn ($c) => '<div class="d-flex w-full justify-content-center align-items-center" style="gap:10px">
                    <a href="javascript:void(0)" data-bind-product-category="' . $c->id . '" class="btn btn-success btn-xs editProductCategory" title="Edit"><i class="ri-pencil-line"></i></a>
                    <a href="javascript:void(0)" onclick="deleteProductCategory(' . $c->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a></div>')
                ->rawColumns(['category', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $category = null;

        try {
            if ($request->filled('id_product_category')) {
                $category = $this->service->find((int) $request->id_product_category);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('category'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->service->create($this->validatePayload($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan kategori.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $this->service->update($id, $this->validatePayload($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah kategori.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_product_category);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus kategori.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
