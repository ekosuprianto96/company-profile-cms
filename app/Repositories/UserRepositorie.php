<?php


namespace App\Repositories;

use App\Models\User;
use App\Models\DetailAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepositori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserRepositorie extends BaseRepositori
{

    protected $model;
    protected $where = [];
    public function __construct()
    {
        $this->setModel(User::class);
        parent::__construct();
    }

    public function dataTable()
    {
        try {

            $table = DataTables::of($this->model->where('id', '<>', Auth::id())->get())
                ->addColumn('nama_lengkap', function ($list) {
                    return $list->account->nama_lengkap;
                })
                ->addColumn('role', function ($list) {
                    return '<span class="badge badge-sm badge-primary">' . $list->role->nama . '</span>';
                })
                ->addColumn('mobile_access', function ($list) {
                    if ($list->mobile_admin_access) {
                        $key = e($list->credential_key ?? '-');

                        return '<span class="badge badge-sm badge-success">Aktif</span>'
                            . '<div class="mt-1 d-flex align-items-center" style="gap:6px;">'
                            . '<code style="font-size:11px;">' . $key . '</code>'
                            . '<a href="javascript:void(0)" onclick="copyCredential(\'' . $key . '\')" title="Salin"><i class="ri-file-copy-line"></i></a>'
                            . '</div>';
                    }

                    return '<span class="badge badge-sm badge-secondary">Nonaktif</span>';
                })
                ->addColumn('tgl_lahir', function ($list) {
                    return $list->account->tanggal_lahir ?? '-';
                })
                ->addColumn('email', function ($list) {
                    return $list->email;
                })
                ->addColumn('no_telp', function ($list) {
                    return $list->account->no_telpon ?? '-';
                })
                ->addColumn('no_ktp', function ($list) {
                    return $list->account->no_ktp ?? '-';
                })
                ->addColumn('nip', function ($list) {
                    return $list->account->no_nip ?? '-';
                })
                ->addColumn('action', function ($list) {
                    $mobileBtn = $list->mobile_admin_access
                        ? '<button type="button" onclick="toggleMobileAccess(' . $list->id . ', false)" class="btn btn-warning btn-xs" title="Cabut Akses Mobile"><i class="ri-smartphone-line"></i></button>'
                        . '<button type="button" onclick="regenerateCredential(' . $list->id . ')" class="btn btn-info btn-xs" title="Regenerate Credential"><i class="ri-refresh-line"></i></button>'
                        : '<button type="button" onclick="toggleMobileAccess(' . $list->id . ', true)" class="btn btn-outline-primary btn-xs" title="Beri Akses Mobile"><i class="ri-smartphone-line"></i></button>';

                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 8px">
                                    ' . $mobileBtn . '
                                    <a href="' . route('admin.pengguna.edit', $list->id) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteUser(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['role', 'nama_lengkap', 'tgl_lahir', 'username', 'email', 'no_telp', 'no_ktp', 'nip', 'mobile_access', 'action'])
                ->make(true);

            return $table;
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], 500);
        }
    }
}
