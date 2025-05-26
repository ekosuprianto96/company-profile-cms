<?php

namespace App\Http\Controllers\Admin\Groups;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\GroupRepositori;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\GroupUpdateRequest;

class GroupController extends Controller
{
    use AdminView;
    protected $groups;
    protected $statusCode = 500;
    public function __construct(GroupRepositori $groups) 
    {
        $this->groups = $groups;
        $this->setView('admin.pages.groups');
    }

    public function index() {
        return $this->view('index');
    }

    public function data() {
        try {
            
            $table = DataTables::of($this->groups->all())
                    ->addColumn('nama', function($list) {
                        return $list->nama;
                    })
                    ->addColumn('total_module', function($list) {
                        return $list->modules->count();
                    })
                    ->addColumn('action', function($list) {
                        return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                    <a href="javascript:void(0)" data-bind-group="'.$list->id_group.'" class="btn btn-success btn-xs editGroup" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteGroup('.$list->id_group.')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                    })
                    ->rawColumns(['nama', 'total_module', 'action'])
                    ->make(true);

            return $table;
        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function storeGroup(GroupStoreRequest $request) {
        try {

            $menu = $this->groups->create($request->only('nama', 'deskripsi', 'an'));
            if(!$menu['status']) {
                $this->statusCode = 402;
                throw new \Exception($menu['message']);
            }

            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan group.'
            ], $this->statusCode);

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function updateGroup(GroupUpdateRequest $request, $id_group) {
        try {
            
            $group = $this->groups->find($id_group);
            if(empty($group)) {
                $this->statusCode = 404;
                throw new \Exception('Group tidak ditemukan.');
            }
      
            $group->update($request->all());
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update menu.',
                'detail' => (object) [
                    'id_menu' => $group->id_group,
                    'nama' => $group->nama
                ]
            ], $this->statusCode);

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request) {
        try {

            if(!key_exists('id_group', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, id_group tidak ditemukan.');
            }

            $menu = $this->groups->delete($request->id_group);

            if($menu) {
                $this->statusCode = 200;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menghapus group'
                ], $this->statusCode);
            }

            $this->statusCode = 422;
            throw new \Exception('Gagal menghapus group, silahkan ulangi proses.');

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function forms(Request $request) {
        $group = null;

        try {

            if(key_exists('id_group', $request->all())) {
                $group = $this->groups->find($request->id_group);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('group'));
        }catch(\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
