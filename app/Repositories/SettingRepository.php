<?php

namespace App\Repositories;

use App\Models\Settings;

class SettingRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Settings::class);
        parent::__construct();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function findByName($id)
    {
        return $this->model->where('name', $id)->first();
    }

    public function update($id, array $param = [])
    {
        $setting = $this->model->find($id);

        if (!$setting) throw new \Exception("Data tidak ditemukan", 404);

        return $setting->update(['value' => json_encode([...json_decode($setting->value, true), ...$param])]);
    }
}
