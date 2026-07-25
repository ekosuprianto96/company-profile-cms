<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'show_service_header', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'show_service_header' => 'boolean'];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Layanan yang memakai form ini. */
    public function services(): HasMany
    {
        return $this->hasMany(MobileService::class, 'form_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
