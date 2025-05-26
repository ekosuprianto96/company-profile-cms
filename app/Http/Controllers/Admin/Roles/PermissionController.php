<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\PermissionRepositori;

class PermissionController extends Controller
{
    use AdminView;
    protected $permission;
    protected $statusCode = 500;
    public function __construct(PermissionRepositori $permission)
    {
        $this->permission = $permission;
        $this->setView('admin.pages.permissions');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {

            $table = DataTables::of($this->permission->where([
                'an' => 1
            ])->get())
                ->addColumn('display_name', function ($list) {
                    return $list->display_name;
                })
                ->addColumn('name', function ($list) {
                    return $list->name;
                })
                ->addColumn('action', function ($list) {
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                    <a href="javascript:void(0)" data-bind-permission="' . $list->id . '" class="btn btn-success btn-xs editPermission" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deletePermission(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['display_name', 'name', 'action'])
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

    public function storePermission(PermissionStoreRequest $request)
    {
        try {

            $menu = $this->permission->create($request->only('display_name', 'name', 'an'));
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

    public function updatePermission(PermissionUpdateRequest $request, $id)
    {
        try {

            $permission = $this->permission->find($id);
            if (empty($permission)) {
                $this->statusCode = 404;
                throw new \Exception('Permission tidak ditemukan.');
            }

            $permission->update($request->all());
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update menu.',
                'detail' => (object) [
                    'id_permission' => $permission->id,
                    'nama' => $permission->display_name
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

            if (!key_exists('id_permission', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, id_permission tidak ditemukan.');
            }

            $permission = $this->permission->delete($request->id_permission);

            if ($permission) {
                $this->statusCode = 200;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menghapus permission'
                ], $this->statusCode);
            }

            $this->statusCode = 422;
            throw new \Exception('Gagal menghapus permission, silahkan ulangi proses.');
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
        $permission = null;

        try {

            if (key_exists('id_permission', $request->all())) {
                $permission = $this->permission->find($request->id_permission);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('permission'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
