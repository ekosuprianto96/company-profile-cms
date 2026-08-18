<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Template Rules Step: kumpulan langkah status pengajuan yang bisa dipakai
 * oleh satu atau banyak layanan (mobile_services.step_template_id).
 */
class StepTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(StepTemplateStep::class)->orderBy('sort_order')->orderBy('id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(MobileService::class, 'step_template_id');
    }
}
