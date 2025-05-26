<?php

namespace App\Repositories;

use App\Models\Banner;

class BannerRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Banner::class);
        parent::__construct();
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
        return $delete->delete();
    }
}
