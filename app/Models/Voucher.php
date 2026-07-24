<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'terms',
        'order_type',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_purchase_amount',
        'item_scope',
        'user_scope',
        'usage_limit',
        'usage_limit_per_user',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'integer',
            'max_discount_amount' => 'integer',
            'min_purchase_amount' => 'integer',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function targetItems(): HasMany
    {
        return $this->hasMany(VoucherTargetItem::class);
    }

    public function targetUsers(): BelongsToMany
    {
        return $this->belongsToMany(MobileUser::class, 'voucher_mobile_user', 'voucher_id', 'mobile_user_id')->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    /** Apakah voucher sudah diambil (claim) oleh user tertentu. */
    public function isClaimedBy(?int $mobileUserId): bool
    {
        if (! $mobileUserId) {
            return false;
        }

        return $this->relationLoaded('claims')
            ? $this->claims->contains('mobile_user_id', $mobileUserId)
            : $this->claims()->where('mobile_user_id', $mobileUserId)->exists();
    }

    /** Jumlah redemption yang masih memakai kuota (reserved atau used). */
    public function activeRedemptionCount(): int
    {
        return $this->redemptions()->whereIn('status', ['reserved', 'used'])->count();
    }

    public function isPercentage(): bool
    {
        return $this->discount_type === 'percentage';
    }
}
