<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileUserAddress extends Model
{
    protected $fillable = [
        'mobile_user_id',
        'label',
        'recipient_name',
        'recipient_phone',
        'address',
        'address_detail',
        'province',
        'regency',
        'district',
        'village',
        'region_label',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'province' => 'array',
            'regency' => 'array',
            'district' => 'array',
            'village' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_primary' => 'boolean',
        ];
    }

    public function mobileUser(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }
}
