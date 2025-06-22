<?php

namespace App\Http\Controllers\Admin\Themes;

use App\Http\Controllers\Controller;
use App\Services\InformasiService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ThemeController extends Controller
{
    use AdminView;

    public function __construct()
    {
        $this->setView('admin.pages.theme-settings');
    }

    public function index(
        InformasiService $informasiService
    ) {
        try {

            $informasi = $informasiService->findByKey('theme_settings')
                ->decode(true)
                ->get();

            return $this->view('index', [
                'themes' => app_themes(),
                'id' => $informasi->id
            ]);
        } catch (\Throwable $th) {
            Alert::error('Error!', $th->getMessage());

            return redirect()->route('admin.informasi');
        }
    }

    public function update(
        Request $request,
        int $id,
        InformasiService $informasiService
    ) {
        try {

            $informasiService
                ->setRequest($request)
                ->findByKey('theme_settings')
                ->addResult('id', $id)
                ->update();

            Alert::success('Sukses!', 'Tema berhasil di update.');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Error!', $th->getMessage());

            return redirect()->back();
        }
    }
}
