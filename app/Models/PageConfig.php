<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penyimpanan konfigurasi page builder & home section (dulu file JSON:
 * config/page.json & config/sections.json) sebagai baris DB dengan key.
 */
class PageConfig extends Model
{
    protected $fillable = ['key', 'value'];
}
