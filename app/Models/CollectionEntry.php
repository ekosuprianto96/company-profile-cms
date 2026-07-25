<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionEntry extends Model
{
    protected $fillable = ['collection_id', 'data', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /** Nilai satu field dari data JSON. */
    public function value(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
