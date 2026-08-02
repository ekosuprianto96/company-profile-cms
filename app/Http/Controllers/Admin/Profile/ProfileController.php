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
        $me = \App\Models\User::with('role')->find(auth()->id());
        return $this->view('index', compact('pengguna', 'me'));
    }

    /** Superadmin: generate/aktifkan credential akses aplikasi admin untuk dirinya sendiri. */
    public function generateCredential()
    {
        try {
            $me = \App\Models\User::with('role')->find(auth()->id());

            if (! $me->isSuperAdmin()) {
                return response()->json(['status' => false, 'message' => 'Hanya superadmin yang dapat generate credential sendiri.'], 403);
            }

            $me->update([
                'credential_key' => \App\Models\User::generateUniqueCredentialKey(),
                'mobile_admin_access' => true,
            ]);
            $me->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Credential berhasil digenerate.',
                'credential_key' => $me->fresh()->credential_key,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
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
