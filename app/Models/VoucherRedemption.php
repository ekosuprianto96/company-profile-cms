<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherRedemption extends Model
{
    protected $fillable = [
        'voucher_id',
        'mobile_user_id',
        'order_type',
        'order_id',
        'base_amount',
        'discount_amount',
        'status',
        'reserved_at',
        'used_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'base_amount' => 'integer',
            'discount_amount' => 'integer',
            'reserved_at' => 'datetime',
            'used_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }
}
