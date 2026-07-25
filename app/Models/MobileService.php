<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class MobileService extends Model
{
    use Blameable;

    protected $table = 'mobile_services';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'is_coming_soon' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** Form pengajuan yang dipakai layanan ini (boleh dipakai bersama layanan lain). */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /** Skema harga layanan (mis. Biaya Survei / Konsultasi / DP). */
    public function priceItems()
    {
        return $this->hasMany(ServicePriceItem::class, 'mobile_service_id')->orderBy('sort_order')->orderBy('id');
    }

    /** Total biaya wajib dari skema harga; null bila layanan belum punya skema. */
    public function requiredPriceTotal(): ?int
    {
        if ($this->priceItems->isEmpty()) {
            return null;
        }

        return (int) $this->priceItems->where('is_required', true)->sum('amount');
    }
}
