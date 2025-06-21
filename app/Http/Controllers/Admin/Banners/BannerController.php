<?php

namespace App\Http\Controllers\Admin\Banners;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\BannerService;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\BannerStoreRequest;
use App\Http\Requests\BannerUpdateRequest;
use App\Http\Controllers\Admin\Modules\Status;

class BannerController extends Controller
{
    use AdminView;
    protected $statusCode = 500;

    public function __construct(
        public BannerService $bannerService
    ) {
        $this->setView('admin.pages.banners');
    }

    public function index()
    {
        try {

            $banners = $this->bannerService->all();
            return $this->view('index', $banners);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function storeBanner(BannerStoreRequest $request)
    {
        try {

            $this->bannerService->create($request);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menambahkan banner'
            ], $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function updateBanner(BannerUpdateRequest $request, $id)
    {
        try {

            $this->bannerService->update($request, $id);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil update banner'
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

            $this->bannerService->delete($request->id_banner);
            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menghapus banner'
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

            return DataTables::of($this->bannerService->all())
                ->addColumn('image', function ($list) {
                    return '<img src="' . image_url('banners', $list->image_url) . '" alt="' . $list->image_url . '">';
                })
                ->addColumn('title', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->title . '</span>';
                })
                ->addColumn('sub_title', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->sub_title . '</span>';
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
                            <a href="javascript:void(0)" data-bind-banner="' . $list->id . '" class="btn btn-success btn-xs editBanner" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteBanner(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['image', 'title', 'sub_title', 'created_by', 'updated_by', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $banner = null;

        try {

            if (key_exists('id_banner', $request->all())) {
                $banner = $this->bannerService->findBanner($request->id_banner);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('banner'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
