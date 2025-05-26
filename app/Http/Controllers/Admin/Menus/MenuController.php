<?php

namespace App\Http\Controllers\Admin\Menus;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\MenuRepositori;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Role;

class MenuController extends Controller
{
    use AdminView;

    protected $menus;
    protected $statusCode = 500;
    public function __construct(MenuRepositori $menus) 
    {
        $this->menus = $menus;
        $this->setView('admin.pages.menus');
    }

    public function index() {
        $menus = $this->menus->all();
        return $this->view('index', compact('menus'));
    }

    public function data() {
        try {
            
            $table = DataTables::of($this->menus->all())
                    ->addColumn('nama', function($list) {
                        return $list->nama;
                    })
                    ->addColumn('module', function($list) {
                        return $list->module->nama;
                    })
                    ->addColumn('icon', function($list) {
                        return $list->icon;
                    })
                    ->addColumn('url', function($list) {
                        return $list->url;
                    })
                    ->addColumn('status', function($list) {
                        if($list->an === 1) {
                            return '<span class="badge badge-success badge-sm">'.(Status::AKTIF->text()).'</span>';
                        }

                        return '<span class="badge badge-danger badge-sm">'.(Status::NONAKTIF->text()).'</span>';;
                    })
                    ->addColumn('action', function($list) {
                        return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                    <a href="javascript:void(0)" data-bind-setting="'.$list->id_menu.'" class="btn btn-warning btn-xs settingMenu" title="Setting Role"><i class="ri-settings-3-line"></i></a>
                                    <a href="javascript:void(0)" data-bind-menu="'.$list->id_menu.'" class="btn btn-success btn-xs editMenu" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteMenu('.$list->id_menu.')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                    })
                    ->rawColumns(['nama', 'module', 'icon', 'url', 'status', 'action'])
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

    public function storeMenu(StoreMenuRequest $request) {
        try {

            $menu = $this->menus->create($request->only('nama', 'deskripsi', 'id_module', 'an', 'url', 'icon'));
            if(!$menu['status']) {
                $this->statusCode = 402;
                throw new \Exception($menu['message']);
            }

            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan menu.'
            ], $this->statusCode);

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function updateMenu(UpdateMenuRequest $request, $id_menu) {
        try {
            
            $menu = $this->menus->find($id_menu);
            if(empty($menu)) {
                $this->statusCode = 404;
                throw new \Exception('Menu tidak ditemukan.');
            }

            if(empty($request->id_module)) {
                $request->merge(['id_module' => 0]);
            }
      
            $menu->update($request->all());
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update menu.',
                'detail' => (object) [
                    'id_menu' => $menu->id_menu,
                    'nama' => $menu->nama
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

    public function attachRoleMenu(Request $request) {
        try {

            $request->validate([
                'roles' => 'required'
            ], [
                'roles.required' => 'Role akses tidak boleh kosong.'
            ]);
            
            $menu = $this->menus->syncRole($request->id_menu, $request->roles);
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil setting Role menu.',
                'detail' => true
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

            if(!key_exists('id_menu', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, id_menu tidak ditemukan.');
            }

            $menu = $this->menus->delete($request->id_menu);

            if($menu) {
                $this->statusCode = 200;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menghapus menu'
                ], $this->statusCode);
            }

            $this->statusCode = 422;
            throw new \Exception('Gagal menghapus menu, silahkan ulangi proses.');

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function forms(Request $request) {
        $menu = null;

        try {

            if(key_exists('id_menu', $request->all())) {
                $menu = $this->menus->find($request->id_menu);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('menu'));
        }catch(\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

}
