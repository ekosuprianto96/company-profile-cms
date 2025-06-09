<?php

namespace App\Http\Controllers\Admin;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\InformasiService;
use App\Http\Controllers\Controller;

class InformasiPageController extends Controller
{
    use AdminView;

    public function __construct(
        protected ?Request $request,
        protected InformasiService $informasiService
    ) {
        $this->setView('admin.pages.informasi');

        if ($this->request) {
            $this->informasiService->setRequest($this->request);
        }
    }

    public function index()
    {
        $data['informasi'] = $this->informasiService
            ->getAllInformasi()
            ->decode(true)
            ->get();

        return $this->view('index', $data);
    }

    public function update(Request $request, $id)
    {
        try {

            $this->informasiService
                ->addResult('id', $id)
                ->update();

            cache()->forget('app_settings');
            cache()->forget('footer_settings');

            return response()->json(['message' => 'Berhasil mengubah informasi'], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function forms(Request $request)
    {
        $informasi = null;

        try {

            if (key_exists('id', $request->all())) {
                $informasi = $this->informasiService
                    ->find($request->id)
                    ->decode(false, false)
                    ->get();
            }

            return $this->setView('admin.components.forms.')->view($request->view . '-' . $informasi->key, compact('informasi'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
