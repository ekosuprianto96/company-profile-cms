<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\ProductOrder;
use Illuminate\Http\Request;

class MobileProductOrderController extends ApiController
{
    public function cancel(Request $request, string $orderNumber)
    {
        $order = ProductOrder::query()->where('order_number', $orderNumber)->first();

        if (! $order) {
            return $this->error('Order tidak ditemukan.', 404);
        }

        // TODO: batasi ke pemilik order saat fitur Order Produk final dibangun.

        if (! $order->canBeCancelled()) {
            return $this->error('Pesanan tidak dapat dibatalkan karena sudah diproses.', 422);
        }

        $order->update([
            'status' => 'cancelled',
            'status_label' => 'Dibatalkan',
            'cancelled_at' => now(),
        ]);

        return $this->success([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'can_cancel' => $order->canBeCancelled(),
                'cancelled_at' => optional($order->cancelled_at)?->toISOString(),
            ],
        ], 'Pesanan berhasil dibatalkan.');
    }
}
