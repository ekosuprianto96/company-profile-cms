<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\ProductOrder;
use App\Repositories\MobileServiceRequestRepository;
use App\Services\MobileInvoicePdfService;
use Illuminate\Http\Request;

class MobileInvoiceController extends ApiController
{
    public function __construct(
        protected MobileServiceRequestRepository $serviceRequests,
        protected MobileInvoicePdfService $invoicePdf,
    ) {}

    public function serviceRequest(Request $request, int $id)
    {
        $serviceRequest = $this->serviceRequests->findByUserAndId($request->user()->id, $id);

        if (! $serviceRequest) {
            return $this->error('Data pengajuan tidak ditemukan.', 404);
        }

        return $this->pdfResponse(
            $this->invoicePdf->forServiceRequest($serviceRequest),
            'invoice-' . ($serviceRequest->transaction_code_label ?? $serviceRequest->id) . '.pdf',
        );
    }

    public function productOrder(Request $request, string $orderNumber)
    {
        $order = ProductOrder::query()->where('order_number', $orderNumber)->first();

        if (! $order) {
            return $this->error('Order tidak ditemukan.', 404);
        }

        // TODO: batasi ke pemilik order saat fitur Order Produk final dibangun.

        return $this->pdfResponse(
            $this->invoicePdf->forProductOrder($order),
            'invoice-' . $order->order_number . '.pdf',
        );
    }

    protected function pdfResponse(string $pdf, string $filename)
    {
        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
