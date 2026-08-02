<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\MobileServiceRequest;
use App\Models\ProductOrder;
use Illuminate\Http\Request;

class PaymentController extends ApiController
{
    /** Daftar bukti pembayaran manual yang menunggu verifikasi (layanan + produk). */
    public function pending(Request $request)
    {
        try {
            $service = MobileServiceRequest::with('user:id,name')
                ->whereNotNull('payment_proof_path')
                ->where('payment_status', '!=', 'paid')
                ->latest('payment_proof_uploaded_at')
                ->limit(50)->get()
                ->map(fn ($sr) => [
                    'type' => 'service',
                    'id' => $sr->id,
                    'code' => $sr->transaction_code_label ?? ('SR-' . $sr->id),
                    'customer' => optional($sr->user)->name ?? '-',
                    'amount' => (int) $sr->total_amount,
                    'method' => $sr->payment_method,
                    'uploaded_at' => optional($sr->payment_proof_uploaded_at)?->toISOString(),
                ]);

            $product = ProductOrder::with('user:id,name')
                ->whereNotNull('payment_proof_path')
                ->where('payment_status', '!=', 'paid')
                ->latest('payment_proof_uploaded_at')
                ->limit(50)->get()
                ->map(fn ($po) => [
                    'type' => 'product',
                    'id' => $po->id,
                    'code' => $po->order_number,
                    'customer' => $po->customer_name ?? optional($po->user)->name ?? '-',
                    'amount' => (int) $po->grand_total,
                    'method' => $po->payment_method,
                    'uploaded_at' => optional($po->payment_proof_uploaded_at)?->toISOString(),
                ]);

            $items = $service->concat($product)->sortByDesc('uploaded_at')->values();

            return $this->success(['pending' => $items, 'total' => $items->count()], 'Menunggu verifikasi pembayaran.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
