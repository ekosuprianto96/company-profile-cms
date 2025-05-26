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

    public function createUserFack()
    {
        DB::beginTransaction();
        try {

            $user = User::create([
                'id_role' => 1,
                'username' => 'examplemitra',
                'email' => 'example-mitra@gmail.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $superAdmin = DetailAccount::create([
                'user_id' => $user->id,
                'no_telpon' => '6567657546456',
                'no_ktp' => 546456456456456,
                'nama_lengkap' => 'Example Mitra',
                'alamat' => 'Jakarta, Kali Baru',
                'tanggal_lahir' => '14-09-1996'
            ]);

            DB::commit();
            dd('Ok');
        } catch (\Exception $err) {
            DB::rollback();
            dd($err->getMessage() . '-' . $err->getLine());
        }
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
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                    <a href="' . route('admin.pengguna.edit', $list->id) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteUser(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['role', 'nama_lengkap', 'tgl_lahir', 'username', 'email', 'no_telp', 'no_ktp', 'nip', 'action'])
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
