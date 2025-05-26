<?php

namespace App\Repositories;

use App\Models\Menus;
use App\Repositories\BaseRepositori;

class MenuRepositori extends BaseRepositori
{
    protected $fillable = [
        'an',
        'nama',
        'id_module',
        'deskripsi',
        'url',
        'icon'
    ];

    public function __construct()
    {
        $this->setModel(Menus::class);
        parent::__construct();
    }

    public function syncRole($id_menu, array $id_role)
    {
        return $this->model::find($id_menu)->roles()->sync($id_role);
    }

    public function all()
    {
        return $this->getAll();
    }
}
