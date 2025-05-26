<?php

namespace App\Repositories;

use App\Models\KategoriBlog;

class KategoriBlogRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(KategoriBlog::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }

    public function update($id, array $param = [])
    {
        $kategori = $this->model->find($id);
        if (!$kategori) throw new \Exception("Data tidak ditemukan", 404);
        return $kategori->update($param);
    }

    public function delete($id)
    {
        $kategori = $this->model->find($id);
        if (!$kategori) throw new \Exception("Data tidak ditemukan", 404);
        return $kategori->delete();
    }
}
