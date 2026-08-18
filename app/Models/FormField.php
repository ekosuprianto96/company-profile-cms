<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'key',
        'label',
        'type',
        'role',
        'placeholder',
        'help_text',
        'is_required',
        'options_source',
        'options_source_key',
        'options',
        'validation',
        'conditional',
        'config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
            'validation' => 'array',
            'conditional' => 'array',
            'config' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** Tipe field yang butuh daftar opsi. */
    public function hasOptions(): bool
    {
        return in_array($this->type, config('form_builder.option_types', []), true);
    }
}
