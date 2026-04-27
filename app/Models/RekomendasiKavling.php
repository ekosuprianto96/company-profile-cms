<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class RekomendasiKavling extends Model
{
    use Blameable;
    protected $table = 'rekomendasi_kavling';
    protected $guarded = ['id'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function images() {
        $images = json_decode((!empty($this->images)) ? $this->images : '[]');
        return $images;
    }
}
