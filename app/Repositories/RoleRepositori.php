<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\BaseRepositori;

class RoleRepositori extends BaseRepositori
{
    protected $fillable = [
        'an',
        'nama',
        'deskripsi'
    ];

    public function __construct()
    {
        $this->setModel(Role::class);
        parent::__construct();
    }

    public function with(array $withs)
    {
        return $this->model->with($withs);
    }

    public function syncMenu(int $id_role, array $id_menu)
    {
        return $this->model->find($id_role)->menus()->sync($id_menu);
    }

    public function syncPermission(int $id_role, array $id_permission)
    {
        return $this->model->find($id_role)->permissions()->sync($id_permission);
    }
}
