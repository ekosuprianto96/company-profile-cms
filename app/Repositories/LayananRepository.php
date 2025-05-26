<?php

namespace App\Repositories;

use App\Models\Layanan;

class LayananRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Layanan::class);
        parent::__construct();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function all()
    {
        return $this->getAll();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }

    public function update($id, array $param = [])
    {
        $update = $this->model->find($id);
        if (!$update) throw new \Exception("Data tidak ditemukan", 404);
        return $update->update($param);
    }

    public function delete($id)
    {
        $delete = $this->model->find($id);
        if (!$delete) throw new \Exception("Data tidak ditemukan", 404);
        return $delete->delete();
    }
}
