<?php

namespace App\Repositories;

use App\Models\EmailSetting;
use App\Models\Settings;

class EmailSettingRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(Settings::class);
        parent::__construct();
    }

    public function getSettings()
    {
        $settings = $this->model->where('name', 'email_config')->first();
        $decodeValue = json_decode($settings->value ?? '{}', true);
        return collect($decodeValue);
    }

    public function updateSettings(array $param = [])
    {
        $settings = $this->model->where('name', 'email_config')->first();
        return $settings->update(['value' => json_encode([...json_decode($settings->value, true), ...$param])]);
    }
}
