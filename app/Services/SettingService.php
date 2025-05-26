<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\SettingRepository;

class SettingService
{
    public function __construct(
        protected SettingRepository $setting
    ) {}

    public function getSetting(?string $key = null)
    {
        if (is_null($key)) return $this->decode($this->setting->all(), true);

        return $this->decode($this->setting->findByName($key), true);
    }

    public function update(int $id, Request $request)
    {

        $request->validate([
            'app_name' => 'required|string|max:50'
        ], [
            'app_name.required' => 'Nama aplikasi tidak boleh kosong.',
            'app_name.string' => 'Nama aplikasi harus berupa string yang valid.',
            'app_name.max' => 'Nama aplikasi tidak boleh lebih dari 50 karakter.'
        ]);


        return $this->setting->update($id, $request->all());
    }

    public function decode($value, $assoc = false)
    {
        if (is_array($value)) {
            return collect($value)->map(function ($item) use ($assoc) {
                return $this->decode($item->value, $assoc);
            });
        }

        return json_decode($value->value, $assoc);
    }
}
