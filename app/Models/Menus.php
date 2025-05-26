<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    use HasFactory;

    protected $table = 'menus';
    protected $guarded = ['id_menu'];
    public $primaryKey = 'id_menu';

    public function roles() {
        return $this->belongsToMany(Role::class, 'menu_role', 'id_menu', 'id_role', 'id_menu', 'id_role');
    }

    public function module() {
        return $this->belongsTo(Modules::class, 'id_module', 'id_module')->orderBy('order', 'asc');
    }
}
