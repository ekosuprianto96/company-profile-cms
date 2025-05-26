<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = ['id_group'];
    public $primaryKey = 'id_group';

    public function modules() {
        return $this->hasMany(Modules::class, 'id_group', 'id_group')->orderBy('order', 'asc');
    }
}
