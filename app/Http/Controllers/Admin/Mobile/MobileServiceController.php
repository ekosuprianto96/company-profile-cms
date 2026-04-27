<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobileServiceStoreRequest;
use App\Http\Requests\MobileServiceUpdateRequest;
use App\Services\MobileServiceAdminService;
use App\Services\MobileServiceNeedTypeAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MobileServiceController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected MobileServiceAdminService $mobileServiceAdminService,
        protected MobileServiceNeedTypeAdminService $mobileServiceNeedTypeAdminService
    ) {
        $this->setView('admin.pages.mobile.services');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->mobileServiceAdminService->queryForAdmin())
                ->addColumn('title', function ($service) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">' . e($service->title) . '</span>
                            <small class="text-muted">' . e($service->slug) . '</small>
                        </div>
                    ';
                })
                ->addColumn('visual', function ($service) {
                    $badge = $service->is_new ? '<span class="badge badge-warning badge-sm">NEW</span>' : '<span class="badge badge-light badge-sm">-</span>';

                    return '
                        <div class="d-flex align-items-center" style="gap:10px;">
                            <span class="rounded-circle border" style="width:24px;height:24px;background:' . e($service->card_color) . ';"></span>
                            <span class="rounded-circle border" style="width:24px;height:24px;background:' . e($service->text_color) . ';"></span>
                            ' . $badge . '
                        </div>
                    ';
                })
                ->addColumn('flags', function ($service) {
                    return '
                        <div class="d-flex flex-column" style="gap:4px;">
                            <span class="badge badge-sm badge-' . ($service->is_featured ? 'info' : 'secondary') . '">Featured: ' . ($service->is_featured ? 'Ya' : 'Tidak') . '</span>
                            <span class="badge badge-sm badge-' . ($service->is_popular ? 'primary' : 'secondary') . '">Popular: ' . ($service->is_popular ? 'Ya' : 'Tidak') . '</span>
                        </div>
                    ';
                })
                ->addColumn('need_types', function ($service) {
                    if ($service->needTypes->isEmpty()) {
                        return '<span class="badge badge-light badge-sm">Belum dipilih</span>';
                    }

                    $badges = $service->needTypes
                        ->map(fn ($needType) => '<span class="badge badge-outline-info badge-sm">' . e($needType->name) . '</span>')
                        ->implode('');

                    return '
                        <div class="d-flex flex-wrap align-items-start" style="gap:6px;max-width:260px;white-space:normal;">
                            ' . $badges . '
                        </div>
                    ';
                })
                ->addColumn('sort_order', fn ($service) => (int) $service->sort_order)
                ->addColumn('status', function ($service) {
                    if ($service->is_active) {
                        return '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('updated_by', function ($service) {
                    return $service->updatedBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('action', function ($service) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-mobile-service="' . $service->id . '" class="btn btn-success btn-xs editMobileService" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteMobileService(' . $service->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['title', 'visual', 'flags', 'need_types', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $service = null;
        $selectedNeedTypeIds = [];

        try {
            if ($request->filled('id_mobile_service')) {
                $service = $this->mobileServiceAdminService->find((int) $request->id_mobile_service);
                $selectedNeedTypeIds = $service->needTypes()
                    ->pluck('mobile_service_need_types.id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();
            }

            $needTypes = $this->mobileServiceNeedTypeAdminService->listActive();

            return $this->setView('admin.components.forms.')
                ->view($request->view, compact('service', 'needTypes', 'selectedNeedTypeIds'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(MobileServiceStoreRequest $request)
    {
        try {
            $this->mobileServiceAdminService->create($request->validated());

            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menambahkan layanan mobile.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function update(MobileServiceUpdateRequest $request, int $id)
    {
        try {
            $this->mobileServiceAdminService->update($id, $request->validated());

            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil mengubah layanan mobile.'
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
            $id = (int) $request->id_mobile_service;
            $this->mobileServiceAdminService->delete($id);

            $this->statusCode = 200;
            return response()->json([
                'message' => 'Berhasil menghapus layanan mobile.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }
}
