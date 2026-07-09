<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobileEventProjectNeed extends Model
{
    use Blameable;

    protected $table = 'mobile_event_project_needs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(MobileEventProjectType::class, 'mobile_event_project_type_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(MobileEventPackage::class, 'mobile_event_project_need_id');
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
