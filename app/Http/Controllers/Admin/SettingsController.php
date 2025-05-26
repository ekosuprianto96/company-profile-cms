<?php

namespace App\Http\Controllers\Admin;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\SettingService;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class SettingsController extends Controller
{
    use AdminView;

    public function __construct(
        protected SettingService $settingService
    ) {
        $this->setView('admin.pages.settings');
    }

    public function index()
    {
        $data['setting'] = $this->settingService->getSetting('app_config');
        return $this->view('index', $data);
    }

    public function update(Request $request, $id)
    {
        try {

            $this->settingService->update($id, $request);

            Alert::success('Sukses', 'Pengaturan aplikasi berhasil di update.');
            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }
}
