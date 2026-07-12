<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mock product order. Akan digantikan/di-extend saat fitur Order Produk penuh
 * dibangun. Untuk sekarang menopang pembuatan invoice PDF produk.
 */
class ProductOrder extends Model
{
    protected $fillable = [
        'mobile_user_id',
        'order_number',
        'product_name',
        'variant',
        'image',
        'quantity',
        'unit_price',
        'subtotal',
        'shipping_fee',
        'grand_total',
        'courier',
        'tracking_number',
        'address',
        'payment_method',
        'payment_status',
        'status',
        'status_label',
        'customer_name',
        'customer_email',
        'customer_phone',
        'cancelled_at',
        'voucher_id',
        'discount_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
        'shipping_fee' => 'integer',
        'grand_total' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    /**
     * Order produk hanya bisa dibatalkan selama masih di tahap awal (diproses/menunggu),
     * yaitu belum dikemas/dikirim/selesai.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['diproses', 'pending', 'menunggu', 'menunggu_pembayaran'], true);
    }
}
