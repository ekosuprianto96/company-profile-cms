<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ModuleRepositori;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use Exception;

class ModuleController extends Controller
{
    use AdminView;

    protected $modules;
    protected $statusCode = 500;
    public function __construct(ModuleRepositori $modules) 
    {
        $this->modules = $modules;
        $this->setView('admin.pages.modules.');
    }

    public function index() {
        $modules = $this->modules->all();
        return $this->view('index', compact('modules'));
    }

    public function data() {
        try {
            
            $table = DataTables::of($this->modules->all())
                    ->addColumn('nama', function($list) {
                        return $list->nama;
                    })
                    ->addColumn('group', function($list) {
                        return $list->group->nama;
                    })
                    ->addColumn('jumlah_menu', function($list) {
                        return $list->menus->count();
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
                                    <a href="javascript:void(0)" data-bind-module="'.$list->id_module.'" class="btn btn-success btn-xs editModule" title="Edit"><i class="ri-pencil-line"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteModule('.$list->id_module.')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                    })
                    ->rawColumns(['nama', 'group', 'jumlah_menu', 'status', 'action'])
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

    public function storeModule(StoreModuleRequest $request) {
        try {

            $module = $this->modules->create($request->only('nama', 'deskripsi', 'id_group', 'an', 'icon'));
            if(!$module['status']) {
                $this->statusCode = 402;
                throw new Exception($module['message']);
            }

            $this->statusCode = 200;
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan module.'
            ], $this->statusCode);

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function updateModule(UpdateModuleRequest $request, $id_module) {
        try {

            $module = $this->modules->find($id_module);
            if(empty($module)) {
                $this->statusCode = 404;
                throw new \Exception('Module tidak ditemukan.');
            }

            $module->update($request->all());
            $this->statusCode = 200;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update module.',
                'detail' => (object) [
                    'id_module' => $module->id_module,
                    'nama' => $module->nama
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

            if(!key_exists('id_module', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, id_module tidak ditemukan.');
            }

            $module = $this->modules->delete($request->id_module);

            if($module) {
                $this->statusCode = 200;
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil menghapus module'
                ], $this->statusCode);
            }

            $this->statusCode = 422;
            throw new \Exception('Gagal menghapus module, silahkan ulangi proses.');

        }catch(\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
                'detail' => null
            ], $this->statusCode);
        }
    }

    public function forms(Request $request) {
        $module = null;

        try {

            if(key_exists('id_module', $request->all())) {
                $module = $this->modules->find($request->id_module);
            }
    
            return $this->setView('admin.components.forms.')->view($request->view, compact('module'));
        }catch(\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
