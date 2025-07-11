<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use Blameable;
    protected $guarded = ['id'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
