<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\ShippingCourierAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ShippingCourierController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ShippingCourierAdminService $service
    ) {
        $this->setView('admin.pages.mobile.shipping-couriers');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('courier', fn ($c) => '<span class="fw-semibold">' . e($c->name) . '</span>' . ($c->code ? '<br><small class="text-muted">' . e($c->code) . '</small>' : ''))
                ->addColumn('type', fn ($c) => $c->is_third_party
                    ? '<span class="badge badge-secondary badge-sm">Jasa kurir (pihak ke-3)</span>'
                    : '<span class="badge badge-success badge-sm">Kurir internal</span>')
                ->addColumn('etd', fn ($c) => e($c->etd ?: '—'))
                ->addColumn('base_cost', fn ($c) => 'Rp' . number_format($c->base_cost, 0, ',', '.'))
                ->addColumn('status', fn ($c) => $c->is_active
                    ? '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>'
                    : '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>')
                ->addColumn('action', fn ($c) => '<div class="d-flex w-full justify-content-center align-items-center" style="gap:10px">
                    <a href="javascript:void(0)" data-bind-shipping-courier="' . $c->id . '" class="btn btn-success btn-xs editShippingCourier" title="Edit"><i class="ri-pencil-line"></i></a>
                    <a href="javascript:void(0)" onclick="deleteShippingCourier(' . $c->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a></div>')
                ->rawColumns(['courier', 'type', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $courier = null;

        try {
            if ($request->filled('id_shipping_courier')) {
                $courier = $this->service->find((int) $request->id_shipping_courier);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('courier'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->service->create($this->validatePayload($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan kurir.'], $this->statusCode);
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

            return response()->json(['message' => 'Berhasil mengubah kurir.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_shipping_courier);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus kurir.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:60'],
            'is_third_party' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'etd' => ['nullable', 'string', 'max:60'],
            'base_cost' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
