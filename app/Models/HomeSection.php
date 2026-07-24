<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeSection extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'source_type',
        'layout',
        'selection_mode',
        'auto_filter',
        'max_items',
        'view_all_target',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_items' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isManual(): bool
    {
        return $this->selection_mode === 'manual';
    }
}
