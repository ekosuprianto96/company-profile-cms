<?php

namespace App\Repositories;

use App\Models\RekomendasiKavling;

class RekomendasiKavlingRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct() {
        $this->setModel(RekomendasiKavling::class);
        parent::__construct();
    }

    public function getAll(?callable $closure = null) {
        $query = $this->model->latest();
        if ($closure) $query = $closure($query);
        return $query->get();
    }

    public function find($id) {
        return $this->model->find($id);
    }

    public function create(array $param = []) {
        return $this->model->create($param);
    }
}
