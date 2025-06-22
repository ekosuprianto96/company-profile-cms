<?php

namespace App\Http\Controllers\Admin\Layanan;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\LayananService;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Requests\LayananStoreRequest;
use App\Http\Requests\LayananUpdateRequest;

class LayananController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        public LayananService $layanan
    ) {
        $this->setView('admin.pages.layanan');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function storeService(LayananStoreRequest $request)
    {
        try {

            $this->layanan->create($request);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menambahkan layanan'
            ], $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function updateService(LayananUpdateRequest $request, $id)
    {
        try {

            $this->layanan->update($request, $id);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil update layanan'
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

            $this->layanan->delete($request->id_service);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menghapus layanan'
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

            return DataTables::of($this->layanan->all())
                ->addColumn('title', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->title . '</span>';
                })
                ->addColumn('slug', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->slug . '</span>';
                })
                ->addColumn('icon', function ($list) {
                    return match ($list->type) {
                        'icon' => '<i class="' . $list->icon . '"></i>',
                        'image' => '<img src="' . (image_url('services', $list->url_image)) . '" alt="' . $list->title . '" style="width: 30px;">'
                    };
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
                            <a href="javascript:void(0)" data-bind-service="' . $list->id . '" class="btn btn-success btn-xs editService" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteService(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['icon', 'title', 'slug', 'created_by', 'updated_by', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $service = null;

        try {

            if (key_exists('id_service', $request->all())) {
                $service = $this->layanan->findLayanan($request->id_service);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('service'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
