<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class KategoriBlog extends Model
{
    use Blameable;

    protected $table = 'kategori_blog';
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
