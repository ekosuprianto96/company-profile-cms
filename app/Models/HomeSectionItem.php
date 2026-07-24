<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSectionItem extends Model
{
    protected $fillable = [
        'home_section_id',
        'item_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }
}
