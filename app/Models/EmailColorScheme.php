<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailColorScheme extends Model
{
    protected $fillable = ['name', 'colors', 'is_default', 'created_by'];

    protected $casts = [
        'colors' => 'array',
        'is_default' => 'boolean',
    ];
}
