<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Exports\ProductOrdersExport;
use App\Http\Controllers\Controller;
use App\Services\MobileInvoicePdfService;
use App\Services\ProductOrderAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProductOrderController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProductOrderAdminService $service,
        protected MobileInvoicePdfService $invoicePdf,
    ) {
        $this->setView('admin.pages.mobile.product-orders');
    }

    public function index()
    {
        return $this->view('index', [
            'statusOptions' => $this->service->statusOptions(),
        ]);
    }

    public function data(Request $request)
    {
        try {
            return DataTables::of($this->service->query($this->filters($request)))
                ->addColumn('order', fn ($o) => '
                    <div class="fw-semibold text-dark">' . e($o->order_number) . '</div>
                    <div class="text-muted" style="font-size:11px;">' . e(optional($o->created_at)->format('d M Y H:i') ?? '-') . '</div>')
                ->addColumn('customer', fn ($o) => '
                    <div class="text-truncate">' . e($o->customer_name ?: optional($o->user)->name ?: '-') . '</div>
                    <div class="text-muted text-truncate" style="font-size:11px;">' . e($o->customer_phone ?: $o->customer_email ?: '-') . '</div>')
                ->addColumn('product', fn ($o) => '
                    <div class="text-truncate">' . e($o->product_name ?: '-') . '</div>
                    <div class="text-muted" style="font-size:11px;">' . (int) $o->items_count . ' item</div>')
                ->addColumn('total', fn ($o) => '<span class="fw-semibold text-nowrap">Rp' . number_format((int) $o->grand_total, 0, ',', '.') . '</span>')
                ->addColumn('status_cell', function ($o) {
                    $pill = fn ($label, $c) => '<span class="badge badge-sm badge-' . $c . '" style="white-space:normal;text-align:left;line-height:1.25;">' . e($label) . '</span>';
                    $statusMap = ['selesai' => 'success', 'dikirim' => 'info', 'dikemas' => 'primary', 'diproses' => 'secondary', 'pending' => 'light', 'cancelled' => 'danger'];
                    $payLabels = ['paid' => 'Lunas', 'pending' => 'Pending', 'failed' => 'Gagal', 'waiting_verification' => 'Verifikasi', 'waiting_transfer' => 'Transfer'];
                    $payColors = ['paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'waiting_verification' => 'warning', 'waiting_transfer' => 'warning'];
                    $payLabel = $payLabels[$o->payment_status] ?? ucfirst(str_replace('_', ' ', (string) $o->payment_status));

                    return '<div class="d-flex flex-column align-items-start" style="gap:4px;">'
                        . $pill($o->status_label ?: ucfirst($o->status), $statusMap[$o->status] ?? 'light')
                        . $pill('Bayar: ' . $payLabel, $payColors[$o->payment_status] ?? 'light')
                        . '</div>';
                })
                ->addColumn('action', fn ($o) => '
                    <div class="d-flex justify-content-center align-items-center" style="gap:8px">
                        <a href="' . route('admin.mobile.product_orders.show', $o->id) . '" class="btn btn-success btn-xs" title="Detail"><i class="ri-eye-line"></i></a>
                        <a href="' . route('admin.mobile.product_orders.invoice', $o->id) . '" target="_blank" class="btn btn-primary btn-xs" title="Invoice"><i class="ri-bill-line"></i></a>
                    </div>')
                ->rawColumns(['order', 'customer', 'product', 'total', 'status_cell', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function show(int $id)
    {
        $order = $this->service->find($id);

        return $this->view('show', [
            'order' => $order,
            'statusOptions' => $this->service->statusOptions(),
        ]);
    }

    /** Invoice PDF: inline (preview/cetak) atau attachment (unduh). */
    public function invoice(Request $request, int $id)
    {
        $order = $this->service->find($id);
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($this->invoicePdf->forProductOrder($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="invoice-' . $order->order_number . '.pdf"',
        ]);
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

    public function exportExcel(Request $request)
    {
        $filename = 'order-produk-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ProductOrdersExport($this->service, $this->filters($request)), $filename);
    }

    public function exportPdf(Request $request)
    {
        $records = $this->service->query($this->filters($request))->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf.product-orders-list', [
            'records' => $records,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->setOption('isRemoteEnabled', false);

        return $pdf->download('order-produk-' . now()->format('Ymd_His') . '.pdf');
    }

    protected function filters(Request $request): array
    {
        return [
            'search' => (string) $request->input('search', ''),
            'status' => (string) $request->input('status', ''),
            'payment_status' => (string) $request->input('payment_status', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
        ];
    }
}
