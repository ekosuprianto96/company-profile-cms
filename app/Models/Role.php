<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = ['id_role'];
    public $primaryKey = 'id_role';

    public function user()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }

    public function menus()
    {
        return $this->belongsToMany(Menus::class, 'menu_role', 'id_role', 'id_menu', 'id_role', 'id_menu')->orderBy('order', 'asc');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles', 'id_role', 'id_permission', 'id_role', 'id');
    }

    public function hasMenu(int $id_menu)
    {
        foreach ($this->menus()->get() as $menu) {
            if ($menu->id_menu === $id_menu) return true;
            continue;
        }

        return false;
    }

    public function hasPermission(int $id_permission)
    {
        return $this->permissions()->where('permissions.id', $id_permission)->exists();
    }
}
