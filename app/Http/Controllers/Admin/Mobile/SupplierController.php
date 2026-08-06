<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\SupplierAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected SupplierAdminService $service
    ) {
        $this->setView('admin.pages.mobile.suppliers');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('supplier', function ($s) {
                    $person = $s->contact_person ? '<small class="text-muted">' . e($s->contact_person) . '</small>' : '';

                    return '<div class="d-flex flex-column"><span class="fw-semibold">' . e($s->name) . '</span>' . $person . '</div>';
                })
                ->addColumn('contact', function ($s) {
                    $rows = [];
                    if ($s->phone) {
                        $rows[] = '<span><i class="ri-phone-line me-1"></i>' . e($s->phone) . '</span>';
                    }
                    if ($s->email) {
                        $rows[] = '<span><i class="ri-mail-line me-1"></i>' . e($s->email) . '</span>';
                    }

                    return $rows ? '<div class="d-flex flex-column" style="font-size:12px;">' . implode('', $rows) . '</div>' : '<span class="text-muted">-</span>';
                })
                ->addColumn('products_count', fn ($s) => '<span class="badge badge-light">' . (int) $s->products_count . ' produk</span>')
                ->addColumn('status', function ($s) {
                    return $s->is_active
                        ? '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>'
                        : '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('action', function ($s) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-supplier="' . $s->id . '" class="btn btn-success btn-xs editSupplier" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteSupplier(' . $s->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['supplier', 'contact', 'products_count', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $supplier = null;

        try {
            if ($request->filled('id_supplier')) {
                $supplier = $this->service->find((int) $request->id_supplier);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('supplier'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->service->create($this->validatePayload($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan suplier.'], $this->statusCode);
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

            return response()->json(['message' => 'Berhasil mengubah suplier.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_supplier);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus suplier.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
