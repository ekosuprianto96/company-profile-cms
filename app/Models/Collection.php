<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'label_field', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CollectionField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Field key yang dipakai sebagai label; fallback ke field pertama. */
    public function labelFieldKey(): ?string
    {
        return $this->label_field ?: $this->fields->first()?->key;
    }
}
