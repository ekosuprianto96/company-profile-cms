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

    public function needTypes()
    {
        return $this->belongsToMany(
            MobileServiceNeedType::class,
            'mobile_service_need_type_relations',
            'mobile_service_id',
            'mobile_service_need_type_id'
        )->withTimestamps();
    }
}
