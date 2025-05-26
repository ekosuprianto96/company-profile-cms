<?php

namespace App\Repositories;

use App\Models\Informasi;

class InformasiRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Informasi::class);
        parent::__construct();
    }

    public function findKey(string $key)
    {
        return $this->model->where('key', $key)->get();
    }
}
