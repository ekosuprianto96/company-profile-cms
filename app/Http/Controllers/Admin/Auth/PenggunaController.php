<?php

namespace App\Http\Controllers\Admin\Auth;

use Throwable;
use App\Traits\AdminView;
use Illuminate\Http\Request;
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
