<?php

namespace App\Repositories;

use App\Models\Settings;

class MobileAppSettingRepository extends BaseRepositori
{
    public function __construct()
    {
        $this->setModel(Settings::class);
        parent::__construct();
    }

    public function getSettings(): array
    {
        return cache()->rememberForever('mobile_app_settings', function () {
            $settings = $this->model->where('name', 'mobile_app_settings')->first();

            return json_decode($settings->value ?? '{}', true) ?: [];
        });
    }

    public function updateSettings(array $param = []): Settings
    {
        $settings = $this->model->updateOrCreate(
            ['name' => 'mobile_app_settings'],
            [
                'value' => json_encode($param),
                'is_global' => 1,
            ]
        );

        if ($settings->wasRecentlyCreated) {
            return $settings;
        }

        $settings->update([
            'value' => json_encode($param),
            'is_global' => 1,
        ]);

        cache()->forget('mobile_app_settings');
        cache()->forget('mobile_app_settings_bootstrap');
        cache()->forget('mobile_request_settings');

        return $settings->refresh();
    }
}
