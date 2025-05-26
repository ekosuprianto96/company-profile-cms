<?php

namespace App\Repositories;

use App\Models\Informasi;
use App\Models\Package;

class PackageRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(Informasi::class);
        parent::__construct();
    }

    public function gatAllPackage(?callable $closure = null)
    {
        $model = $this->model->where('key', 'paket')->first();
        $toCollect = collect(json_decode($model->value, true));
        if ($closure) $toCollect = $closure($toCollect);

        return $toCollect;
    }

    public function findPackage(string $id)
    {
        $model = $this->model->where('key', 'paket')->first();
        $toCollect = collect(json_decode($model->value, true))->where('id', $id)->first();

        return $toCollect;
    }

    public function create(array $param = [])
    {
        $model = $this->model->where('key', 'paket')->first();
        $toCollect = collect(json_decode($model->value, true));

        $toCollect->push($param);

        $model->update(['value' => $toCollect->toArray()]);

        return $model;
    }

    public function updatePackage(string $id, array $param = [])
    {
        $model = $this->model->where('key', 'paket')->first();
        $toCollect = collect(json_decode($model->value, true));

        $toCollect->transform(function ($item) use ($id, $param) {
            if ($item['id'] === $id) {
                $item = [
                    ...$item,
                    ...$param
                ];
            }

            return $item;
        });

        $model->update([
            'value' => $toCollect
        ]);

        return $model;
    }

    public function deletePackage(string $id)
    {
        $model = $this->model->where('key', 'paket')->first();
        $toCollect = collect(json_decode($model->value, true))->where('id', '!=', $id);

        $model->update([
            'value' => $toCollect
        ]);

        return $model;
    }
}
