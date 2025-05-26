<?php

namespace App\Repositories;

use App\Models\Menus;

class BaseRepositori
{

    protected $fillable;
    protected $model;

    public function __construct()
    {
        if (!$this->model) {
            throw new \Exception("Model belum di set", 500);
        }
    }

    public function __call($name, $arguments)
    {
        if ($this->model) {
            return $this->model->{$name}(...$arguments);
        }

        throw new \Exception("Model belum di set", 500);
    }

    public function setModel($model)
    {
        $this->model = app($model);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function find(int|string $id_module)
    {
        return $this->model->find($id_module);
    }

    public function delete(int|string $id_module)
    {
        return $this->find($id_module)->delete();
    }

    public function update(int|string $id_module, array $param = [])
    {
        try {

            foreach ($param as $key => $value) {
                if (!in_array($key, $this->fillable)) throw new \Exception("Properti $key belum di set ke fillable.", 500);
                continue;
            }

            $update = $this->find($id_module);

            if (!$update) throw new \Exception("Data tidak ditemukan", 404);

            $update->update($param);

            return [
                'status' => true,
                'message' => 'success',
                'detail' => $update,
                'code' => 200
            ];
        } catch (\Exception $err) {
            return [
                'status' => false,
                'message' => $err->getMessage(),
                'code' => $err->getCode()
            ];
        }
    }

    public function create(array $param = [])
    {
        try {

            foreach ($param as $key => $value) {
                if (!in_array($key, $this->fillable)) throw new \Exception("Properti $key belum di set ke fillable.", 500);
                continue;
            }

            $create = $this->model->create($param);
            return [
                'status' => true,
                'message' => 'success',
                'detail' => $create,
                'code' => 200
            ];
        } catch (\Exception $err) {
            return [
                'status' => false,
                'message' => $err->getMessage(),
                'code' => $err->getCode()
            ];
        }
    }
}
