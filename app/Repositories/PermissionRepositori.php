<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\BaseRepositori;

class PermissionRepositori extends BaseRepositori
{
    protected $fillable = [
        'an',
        'display_name',
        'name'
    ];

    public function __construct()
    {
        $this->setModel(Permission::class);
        parent::__construct();
    }

    public function with(array $withs)
    {
        return $this->model->with($withs);
    }

    public function all()
    {
        return $this->getAll();
    }
}
