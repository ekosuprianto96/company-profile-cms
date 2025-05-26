<?php

namespace App\Repositories;

use App\Models\Modules;
use App\Repositories\BaseRepositori;

class ModuleRepositori extends BaseRepositori
{
    protected $fillable = [
        'an',
        'nama',
        'id_group',
        'deskripsi',
        'icon'
    ];

    public function __construct()
    {
        $this->setModel(Modules::class);
        parent::__construct();
    }

    public function all()
    {
        return $this->getAll();
    }
}
