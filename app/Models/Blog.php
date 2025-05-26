<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
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

    public function kategori()
    {
        return $this->belongsTo(KategoriBlog::class, 'kategori_id', 'id');
    }
}
