<?php

namespace App\Http\Controllers\Admin\Auth;

use Throwable;
use App\Models\User;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\PenggunaService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepositorie;
use RealRashid\SweetAlert\Facades\Alert;

class PenggunaController extends Controller
{
    use AdminView;
    protected $user;
    protected $statusCode = 500;
    public function __construct(
        UserRepositorie $user,
        private PenggunaService $pengguna
    ) {
        $this->user = $user;
        $this->setView('admin.pages.pengguna');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function create()
    {
        return $this->view('create');
    }

    public function edit($id)
    {

        $pengguna = $this->pengguna->getPengguna($id);
        return $this->view('edit', compact('pengguna'));
    }

    public function data(Request $request)
    {
        return $this->user->dataTable();
    }

    public function storePengguna(Request $request)
    {
        $pengguna = $this->pengguna
            ->setRequest($request)
            ->validate();

        try {


            DB::transaction(function () use ($pengguna) {
                $pengguna->create();
            });

            $this->statusCode = 200;

            Alert::success('Berhasil', 'Berhasil menambahkan pengguna.');

            return redirect()->route('admin.pengguna.index');
        } catch (Throwable $err) {
            Alert::error('Gagal', $err->getMessage());

            return redirect()->back();
        }
    }

    public function updatePengguna(Request $request, $id)
    {
        $pengguna = $this->pengguna
            ->setRequest($request)
            ->validate(true);

        try {

            DB::transaction(function () use ($pengguna, $id) {
                $pengguna->update($id);
            });

            $this->statusCode = 200;

            Alert::success('Berhasil', 'Berhasil mengubah pengguna.');

            return redirect()->route('admin.pengguna.index');
        } catch (\Exception $err) {
            Alert::error('Gagal', $err->getMessage());

            return redirect()->back();
        }
    }

    /** Beri/cabut akses aplikasi Admin (mobile). Saat diberi & belum ada, credential digenerate otomatis. */
    public function toggleMobileAccess($id)
    {
        try {
            $user = User::findOrFail($id);
            $granting = ! $user->mobile_admin_access;

            $updates = ['mobile_admin_access' => $granting];
            if ($granting && empty($user->credential_key)) {
                $updates['credential_key'] = $this->generateCredentialKey();
            }
            $user->update($updates);

            // Akses dicabut → cabut sesi app admin.
            if (! $granting) {
                $user->tokens()->delete();
            }

            return response()->json([
                'status' => true,
                'message' => $granting ? 'Akses aplikasi admin diberikan.' : 'Akses aplikasi admin dicabut.',
                'mobile_admin_access' => $granting,
                'credential_key' => $user->fresh()->credential_key,
            ]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()], 500);
        }
    }

    /** Generate ulang credential key (mis. bila bocor). Sesi app admin dicabut agar login ulang. */
    public function regenerateCredential($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['credential_key' => $this->generateCredentialKey()]);
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Credential key berhasil digenerate ulang.',
                'credential_key' => $user->fresh()->credential_key,
            ]);
        } catch (\Throwable $err) {
            return response()->json(['status' => false, 'message' => $err->getMessage()], 500);
        }
    }

    private function generateCredentialKey(): string
    {
        do {
            $key = 'MJ-ADM-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(3));
        } while (User::where('credential_key', $key)->exists());

        return $key;
    }

    public function destroy(Request $request)
    {
        try {

            $this->pengguna
                ->setRequest($request)
                ->delete();

            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus pengguna'
            ], $this->statusCode);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }
}
