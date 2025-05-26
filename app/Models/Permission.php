<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = ['id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_roles', 'id_permission', 'id_role', 'id', 'id_role');
    }

    public function hasRole(int $idrole)
    {
        return $this->roles()->where('roles.id_role', $idrole)->exists();
    }
}
