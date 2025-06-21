<?php

namespace App\Http\Controllers\Admin\Gallery;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\GalleryService;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\GalleryStoreRequest;
use App\Http\Requests\GalleryUpdateRequest;
use App\Http\Controllers\Admin\Modules\Status;

class GalleryController extends Controller
{
    use AdminView;

    protected $statusCode = 500;
    public function __construct(
        public GalleryService $galleryService
    ) {
        $this->setView('admin.pages.galleries');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function storeGallery(GalleryStoreRequest $request)
    {
        try {

            $this->galleryService->create($request);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menambahkan galeri'
            ], $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function updateGallery(GalleryUpdateRequest $request, $id)
    {
        try {

            $this->galleryService->update($request, $id);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil update galeri'
            ], $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $this->galleryService->delete($request->id_gallery);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menghapus galeri'
            ], $this->statusCode);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage()
            ], $this->statusCode);
        }
    }

    public function data(Request $request)
    {
        try {

            return DataTables::of($this->galleryService->all())
                ->addColumn('image', function ($list) {
                    return '<img src="' . image_url('galleries', $list->image) . '" alt="' . $list->image . '">';
                })
                ->addColumn('title', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->title . '</span>';
                })
                ->addColumn('slug', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->slug . '</span>';
                })
                ->addColumn('created_by', function ($list) {
                    return $list->createdBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('updated_by', function ($list) {
                    return $list->updatedBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('status', function ($list) {
                    if ($list->an == 1) {
                        return '<span class="badge badge-success badge-sm">' . (Status::AKTIF->text()) . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . (Status::NONAKTIF->text()) . '</span>';;
                })
                ->addColumn('action', function ($list) {
                    return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-galeri="' . $list->id . '" class="btn btn-success btn-xs editGaleri" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteGallery(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['image', 'title', 'slug', 'created_by', 'updated_by', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $gallery = null;

        try {

            if (key_exists('id_galeri', $request->all())) {
                $gallery = $this->galleryService->findGallery($request->id_galeri);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('gallery'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
