<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'shipping_courier_id',
        'mobile_user_address_id',
        'service_request_id',
        'notes',
        'tracking_number',
        'address',
        'payment_method',
        'payment_gateway_provider',
        'payment_status',
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'payment_method_selected_at',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_transaction_status',
        'midtrans_payment_type',
        'payment_reference',
        'payment_payload',
        'paid_at',
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
        'discount_amount' => 'integer',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
        'payment_method_selected_at' => 'datetime',
        'payment_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductOrderItem::class);
    }

    public function shippingCourier(): BelongsTo
    {
        return $this->belongsTo(ShippingCourier::class, 'shipping_courier_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_order_id');
    }

    /** Order sudah selesai → produk di dalamnya boleh dinilai. */
    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
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
