<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDesign extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'description', 'subject', 'preheader',
        'html', 'design_json', 'is_active', 'is_default',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Template notifikasi yang memakai desain ini. */
    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class, 'email_design_id');
    }

    /** Apakah desain punya slot {{ body }} (tempat isi notifikasi). Wajib agar isi tak hilang. */
    public function hasBodySlot(): bool
    {
        return (bool) preg_match('/\{\{\s*body\s*\}\}/', (string) $this->html);
    }
}
