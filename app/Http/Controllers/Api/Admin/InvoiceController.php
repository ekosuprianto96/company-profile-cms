<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\MobileServiceRequest;
use App\Models\ProductOrder;
use App\Models\User;
use App\Services\MobileInvoicePdfService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Invoice PDF untuk app admin. Rute publik dengan token via query (`?t=`) agar bisa
 * dibuka langsung oleh in-app browser (Custom Tabs) yang tak bisa mengirim header.
 * Token divalidasi manual + wajib admin dengan akses mobile.
 */
class InvoiceController extends ApiController
{
    public function __construct(protected MobileInvoicePdfService $invoicePdf) {}

    public function show(Request $request, string $type, int $id)
    {
        $plain = (string) $request->query('t', '');
        $token = $plain !== '' ? PersonalAccessToken::findToken($plain) : null;
        $admin = $token?->tokenable;

        if (! $admin instanceof User || ! $admin->canAccessMobileAdmin()) {
            return response('Unauthorized', 401);
        }

        if ($type === 'product') {
            $order = ProductOrder::find($id);
            if (! $order) {
                return response('Not found', 404);
            }
            $pdf = $this->invoicePdf->forProductOrder($order);
            $filename = 'invoice-' . $order->order_number . '.pdf';
        } else {
            $order = MobileServiceRequest::find($id);
            if (! $order) {
                return response('Not found', 404);
            }
            $pdf = $this->invoicePdf->forServiceRequest($order);
            $filename = 'invoice-' . ($order->transaction_code_label ?? $order->id) . '.pdf';
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
