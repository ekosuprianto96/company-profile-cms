<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    use HasFactory;

    protected $guarded = ['id_module'];
    public $primaryKey = 'id_module';

    public function group() {
        return $this->belongsTo(Group::class, 'id_group', 'id_group')->orderBy('order', 'asc');
    }

    public function menus() {
        return $this->hasMany(Menus::class, 'id_module', 'id_module')->orderBy('order', 'asc');
    }
}
