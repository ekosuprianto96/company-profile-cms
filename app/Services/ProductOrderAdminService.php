<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\VoucherRedemption;
use Illuminate\Support\Facades\DB;

class ProductOrderAdminService
{
    protected array $labels = [
        'pending' => 'Menunggu',
        'diproses' => 'Diproses',
        'dikemas' => 'Dikemas',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function queryForAdmin()
    {
        return ProductOrder::query()->withCount('items')->with('user:id,name')->orderByDesc('id');
    }

    public function find(int $id): ProductOrder
    {
        return ProductOrder::query()->with(['items', 'user'])->findOrFail($id);
    }

    public function updateStatus(int $id, array $payload): ProductOrder
    {
        return DB::transaction(function () use ($id, $payload) {
            $order = $this->find($id);
            $newStatus = $payload['status'] ?? $order->status;
            $newPaymentStatus = $payload['payment_status'] ?? $order->payment_status;

            // Dibatalkan → kembalikan stok + lepas voucher (jika belum cancelled)
            if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::query()->where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }
                VoucherRedemption::query()
                    ->where('order_type', 'product')->where('order_id', $order->id)->where('status', 'reserved')
                    ->update(['status' => 'released', 'released_at' => now()]);
            }

            // Dibayar (baru) → tandai voucher used + sold_count + paid_at
            if ($newPaymentStatus === 'paid' && $order->payment_status !== 'paid') {
                VoucherRedemption::query()
                    ->where('order_type', 'product')->where('order_id', $order->id)->where('status', 'reserved')
                    ->update(['status' => 'used', 'used_at' => now()]);
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::query()->where('id', $item->product_id)->increment('sold_count', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => $newStatus,
                'status_label' => $this->labels[$newStatus] ?? ucfirst($newStatus),
                'payment_status' => $newPaymentStatus,
                'paid_at' => ($newPaymentStatus === 'paid' && ! $order->paid_at) ? now() : $order->paid_at,
                'tracking_number' => $payload['tracking_number'] ?? $order->tracking_number,
            ]);

            return $order->fresh(['items', 'user']);
        });
    }
}
