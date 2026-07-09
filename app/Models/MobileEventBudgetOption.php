<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class MobileEventBudgetOption extends Model
{
    use Blameable;

    protected $table = 'mobile_event_budget_options';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'min_amount' => 'integer',
            'max_amount' => 'integer',
            'is_active' => 'boolean',
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
}
