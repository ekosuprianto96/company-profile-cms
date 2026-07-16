<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\ProductOrderAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProductOrderController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProductOrderAdminService $service
    ) {
        $this->setView('admin.pages.mobile.product-orders');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('order', fn ($o) => '<div class="d-flex flex-column"><span class="fw-semibold">' . e($o->order_number) . '</span><small class="text-muted">' . e($o->customer_name ?: optional($o->user)->name ?: '-') . '</small></div>')
                ->addColumn('items', fn ($o) => (int) $o->items_count . ' item')
                ->addColumn('total', fn ($o) => 'Rp' . number_format($o->grand_total, 0, ',', '.'))
                ->addColumn('payment', fn ($o) => $o->payment_status === 'paid'
                    ? '<span class="badge badge-success badge-sm">Lunas</span>'
                    : '<span class="badge badge-warning badge-sm">' . e(ucfirst($o->payment_status)) . '</span>')
                ->addColumn('status', function ($o) {
                    $map = ['selesai' => 'success', 'dikirim' => 'info', 'dikemas' => 'primary', 'diproses' => 'secondary', 'cancelled' => 'danger'];
                    $cls = $map[$o->status] ?? 'light';

                    return '<span class="badge badge-' . $cls . ' badge-sm">' . e($o->status_label ?: ucfirst($o->status)) . '</span>';
                })
                ->addColumn('date', fn ($o) => optional($o->created_at)->format('d M Y'))
                ->addColumn('action', fn ($o) => '<a href="javascript:void(0)" data-bind-product-order="' . $o->id . '" class="btn btn-primary btn-xs detailProductOrder" title="Detail"><i class="ri-eye-line"></i></a>')
                ->rawColumns(['order', 'payment', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        try {
            $order = $this->service->find((int) $request->id_product_order);

            return $this->setView('admin.components.forms.')->view($request->view, compact('order'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['required', 'in:pending,diproses,dikemas,dikirim,selesai,cancelled'],
                'payment_status' => ['required', 'in:pending,paid,failed'],
                'tracking_number' => ['nullable', 'string', 'max:100'],
            ]);
            $this->service->updateStatus($id, $validated);
            $this->statusCode = 200;

            return response()->json(['message' => 'Status pesanan diperbarui.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }
}
