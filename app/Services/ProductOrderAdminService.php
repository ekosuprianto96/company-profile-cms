<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\VoucherRedemption;
use Illuminate\Support\Facades\DB;

class ProductOrderAdminService
{
    protected array $labels = [
        'menunggu_pembayaran' => 'Menunggu bayar',
        'diproses' => 'Diproses',
        'dikemas' => 'Dikemas',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function queryForAdmin(array $filters = [])
    {
        return $this->query($filters);
    }

    /** Query terfilter untuk tabel, export, dan PDF. */
    public function query(array $filters = [])
    {
        $query = ProductOrder::query()->withCount('items')->with('user:id,name')->orderByDesc('id');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status']) && array_key_exists($filters['status'], $this->labels)) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['payment_status']) && in_array($filters['payment_status'], ['pending', 'paid', 'failed'], true)) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /** @return array<int, array{value:string,label:string}> */
    public function statusOptions(): array
    {
        return collect($this->labels)
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()->all();
    }

    /** @return array<string,string> */
    public function statusLabels(): array
    {
        return $this->labels;
    }

    public function find(int $id): ProductOrder
    {
        return ProductOrder::query()->with(['items', 'user', 'shippingCourier'])->findOrFail($id);
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

            $tracking = $payload['tracking_number'] ?? $order->tracking_number;

            // Kurir internal (tanpa jasa kurir pihak ke-3): nomor resi dibuat
            // OTOMATIS saat pesanan berstatus "dikirim" — admin tak perlu mengetik.
            if ($newStatus === 'dikirim' && empty($order->shipping_courier_id) && empty($tracking)) {
                $tracking = $this->generateInternalTrackingNumber($order);
            }

            $order->update([
                'status' => $newStatus,
                'status_label' => $this->labels[$newStatus] ?? ucfirst($newStatus),
                'payment_status' => $newPaymentStatus,
                'paid_at' => ($newPaymentStatus === 'paid' && ! $order->paid_at) ? now() : $order->paid_at,
                'tracking_number' => $tracking,
            ]);

            return $order->fresh(['items', 'user']);
        });
    }

    /** Nomor resi kurir internal — deterministik & unik per pesanan. */
    private function generateInternalTrackingNumber(ProductOrder $order): string
    {
        return 'MJ-INT-'
            . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)
            . '-' . strtoupper(substr(md5($order->order_number . '|' . $order->id), 0, 4));
    }
}
