<?php

namespace App\Http\Controllers\Admin\Blogs;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\KategoriBlogService;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Requests\KategoriStoreRequest;

class KategoriController extends Controller
{
    use AdminView;

    protected $statusCode = 500;
    public function __construct(
        public KategoriBlogService $kategori
    ) {
        $this->setView('admin.pages.blogs');
    }

    public function index()
    {
        return $this->view('kategori');
    }

    public function storeKategori(KategoriStoreRequest $request)
    {
        try {

            $this->kategori->create($request);
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Kategori berhasil disimpan'
            ], $this->statusCode);
        } catch (\Exception $er) {
            return response()->json([
                'message' => $er->getMessage()
            ], $this->statusCode);
        }
    }

    public function updateKategori(KategoriStoreRequest $request, $id)
    {
        try {

            $this->kategori->update($request, $id);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Kategori berhasil disimpan'
            ], $this->statusCode);
        } catch (\Exception $er) {
            return response()->json([
                'message' => $er->getMessage()
            ], $this->statusCode);
        }
    }

    public function data(Request $request)
    {
        try {

            return DataTables::of($this->kategori->all())
                ->addColumn('name', function ($list) {
                    return $list->name;
                })
                ->addColumn('slug', function ($list) {
                    return $list->slug;
                })
                ->addColumn('created_by', function ($list) {
                    return $list->createdBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('updated_by', function ($list) {
                    return $list->updatedBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('status', function ($list) {
                    if ($list->an === 1) {
                        return '<span class="badge badge-success badge-sm">' . (Status::AKTIF->text()) . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . (Status::NONAKTIF->text()) . '</span>';;
                })
                ->addColumn('action', function ($list) {
                    return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-kategori="' . $list->id . '" class="btn btn-success btn-xs editKategori" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteKategori(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['name', 'slug', 'created_by', 'updated_by', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $this->kategori->delete($request->id_kategori);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Kategori berhasil dihapus'
            ], $this->statusCode);
        } catch (\Exception $er) {
            return response()->json([
                'message' => $er->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $kategori = null;

        try {

            if (key_exists('id_kategori', $request->all())) {
                $kategori = $this->kategori->findKategori($request->id_kategori);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('kategori'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
