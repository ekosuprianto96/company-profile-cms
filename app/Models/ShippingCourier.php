<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCourier extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_third_party',
        'is_active',
        'etd',
        'base_cost',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_third_party' => 'boolean',
            'is_active' => 'boolean',
            'base_cost' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
