<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobileServiceNeedTypeStoreRequest;
use App\Http\Requests\MobileServiceNeedTypeUpdateRequest;
use App\Services\MobileServiceNeedTypeAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MobileServiceNeedTypeController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected MobileServiceNeedTypeAdminService $mobileServiceNeedTypeAdminService
    ) {
        $this->setView('admin.pages.mobile.service-need-types');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->mobileServiceNeedTypeAdminService->queryForAdmin())
                ->addColumn('name', function ($needType) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">' . e($needType->name) . '</span>
                            <small class="text-muted">' . e($needType->slug) . '</small>
                        </div>
                    ';
                })
                ->addColumn('description', fn ($needType) => e($needType->description ?? '-'))
                ->addColumn('sort_order', fn ($needType) => (int) $needType->sort_order)
                ->addColumn('status', function ($needType) {
                    if ($needType->is_active) {
                        return '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('action', function ($needType) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-mobile-need-type="' . $needType->id . '" class="btn btn-success btn-xs editMobileNeedType" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteMobileNeedType(' . $needType->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $needType = null;

        try {
            if ($request->filled('id_mobile_need_type')) {
                $needType = $this->mobileServiceNeedTypeAdminService->find((int) $request->id_mobile_need_type);
            }

            return $this->setView('admin.components.forms.')
                ->view($request->view, compact('needType'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(MobileServiceNeedTypeStoreRequest $request)
    {
        try {
            $this->mobileServiceNeedTypeAdminService->create($request->validated());
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil menambahkan jenis kebutuhan layanan.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function update(MobileServiceNeedTypeUpdateRequest $request, int $id)
    {
        try {
            $this->mobileServiceNeedTypeAdminService->update($id, $request->validated());
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil mengubah jenis kebutuhan layanan.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = (int) $request->id_mobile_need_type;
            $this->mobileServiceNeedTypeAdminService->delete($id);
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil menghapus jenis kebutuhan layanan.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }
}

