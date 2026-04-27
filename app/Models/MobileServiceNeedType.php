<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class MobileServiceNeedType extends Model
{
    use Blameable;

    protected $table = 'mobile_service_need_types';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function services()
    {
        return $this->belongsToMany(
            MobileService::class,
            'mobile_service_need_type_relations',
            'mobile_service_need_type_id',
            'mobile_service_id'
        )->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

