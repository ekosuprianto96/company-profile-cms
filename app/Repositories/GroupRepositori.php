<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\BaseRepositori;

class GroupRepositori extends BaseRepositori
{
    protected $fillable = [
        'an',
        'nama',
        'deskripsi'
    ];

    public function __construct()
    {
        $this->setModel(Group::class);
        parent::__construct();
    }

    public function all()
    {
        return $this->getAll();
    }
}
