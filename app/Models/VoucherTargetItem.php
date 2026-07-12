<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherTargetItem extends Model
{
    protected $fillable = [
        'voucher_id',
        'target_type',
        'target_id',
    ];

    protected function casts(): array
    {
        return [
            'target_id' => 'integer',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
