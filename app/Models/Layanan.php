<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use Blameable;

    protected $table = 'services';
    protected $guarded = ['id'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
