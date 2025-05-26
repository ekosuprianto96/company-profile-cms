<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\PenggunaService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileController extends Controller
{
    use AdminView;

    public function __construct(
        private PenggunaService $penggunaService
    ) {
        $this->setView('admin.pages.profile.');
    }

    public function index()
    {
        $pengguna = $this->penggunaService->getPengguna(auth()->user()->id);
        return $this->view('index', compact('pengguna'));
    }

    public function update(Request $request)
    {

        $pengguna = $this->penggunaService->setRequest($request)->validate(true, true);
        try {

            DB::transaction(function () use ($pengguna) {
                $pengguna->updateProfile(auth()->user()->id);
            });

            Alert::success('Berhasil', 'Berhasil update profile.');

            return redirect()->back();
        } catch (\Throwable $e) {
            Alert::error('Gagal', $e->getMessage());

            return redirect()->back();
        }
    }

    public function uploadAvatar(Request $request)
    {
        try {

            $this->penggunaService
                ->setRequest($request)
                ->uploadAvatar();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil upload avatar'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
