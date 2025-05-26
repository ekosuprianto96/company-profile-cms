<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Repositories\RoleRepositori;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    use AdminView;
    protected $roles;
    protected $statusCode = 500;
    public function __construct(RoleRepositori $roles)
    {
        $this->roles = $roles;
        $this->setView('admin.pages.roles');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function attachPermission(Request $request)
    {
        try {

            $role = $this->roles->find($request->id_role);
            if (empty($role)) {
                $this->statusCode = 404;
                throw new \Exception('Group tidak ditemukan.');
            }

            $test = $this->roles->syncPermission($request->id_role, $request->input('permissions', []));
            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan permission.'
            ], $this->statusCode);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function data()
    {
        try {

            $table = DataTables::of($this->roles->with(['menus'])->get())
                ->addColumn('nama', function ($list) {
                    return $list->nama;
                })
                ->addColumn('total_akses', function ($list) {
                    return $list->menus->count();
                })
                ->addColumn('action', function ($list) {
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                    <a href="javascript:void(0)" data-bind-role="' . $list->id_role . '" class="btn btn-warning btn-xs settingRole" title="Setting Permission"><i class="ri-settings-3-line"></i></a>
                                    <a href="javascript:void(0)" data-bind-role="' . $list->id_role . '" class="btn ' . ($list->nama === 'superadmin' ? 'disabled' : '') . ' btn-success btn-xs editRole" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteRole(' . $list->id_role . ')" class="btn ' . ($list->nama === 'superadmin' ? 'disabled' : '') . ' btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['nama', 'total_akses', 'action'])
                ->make(true);

            return $table;
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function storeRole(RoleStoreRequest $request)
    {
        try {

            $menu = $this->roles->create($request->only('nama', 'deskripsi', 'an'));
            if (!$menu['status']) {
                $this->statusCode = 402;
                throw new \Exception($menu['message']);
            }

            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan role.'
            ], $this->statusCode);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function updateRole(RoleUpdateRequest $request, $id_role)
    {
        try {

            $role = $this->roles->find($id_role);
            if (empty($role)) {
                $this->statusCode = 404;
                throw new \Exception('Group tidak ditemukan.');
            }

            $role->update($request->all());
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update menu.',
                'detail' => (object) [
                    'id_role' => $role->id_role,
                    'nama' => $role->nama
                ]
            ], $this->statusCode);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {

            if (!key_exists('id_role', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, id_role tidak ditemukan.');
            }

            $role = $this->roles->delete($request->id_role);

            if ($role) {
                $this->statusCode = 200;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menghapus role'
                ], $this->statusCode);
            }

            $this->statusCode = 422;
            throw new \Exception('Gagal menghapus role, silahkan ulangi proses.');
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $role = null;

        try {

            if (key_exists('id_role', $request->all())) {
                $role = $this->roles->find($request->id_role);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('role'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
