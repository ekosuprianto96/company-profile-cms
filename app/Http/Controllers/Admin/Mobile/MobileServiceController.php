<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobileServiceStoreRequest;
use App\Http\Requests\MobileServiceUpdateRequest;
use App\Models\Category;
use App\Models\MobileService;
use App\Services\MobileServiceAdminService;
use App\Traits\AdminView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MobileServiceController extends Controller
{
    use AdminView;

    /** Jumlah tile layanan yang tampil di grid home mobile (samakan dgn ServiceGrid MAX_TILES). */
    private const HOME_TILE_LIMIT = 7;

    protected $statusCode = 500;

    public function __construct(
        protected MobileServiceAdminService $mobileServiceAdminService
    ) {
        $this->setView('admin.pages.mobile.services');
    }

    public function index()
    {
        return $this->view('index', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function data(Request $request)
    {
        try {
            $query = $this->mobileServiceAdminService->queryForAdmin();
            $this->applyFilters($query, $request);

            return DataTables::of($query)
                ->addColumn('title', function ($service) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">' . e($service->title) . '</span>
                            <small class="text-muted">' . e($service->slug) . '</small>
                            <span class="badge badge-sm ' . (($service->request_flow_type ?? 'standard') === 'event_project' ? 'badge-info' : 'badge-light') . '">' . e($service->request_flow_type ?? 'standard') . '</span>
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
                            <a href="' . route('admin.mobile.services.edit', $service->id) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteMobileService(' . $service->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['title', 'visual', 'flags', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    /**
     * Terapkan filter daftar layanan. Kategori memakai seluruh keturunannya, jadi
     * memilih kategori induk ikut menampilkan layanan di sub-kategorinya.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('category_id')) {
            $category = Category::find((int) $request->input('category_id'));
            if ($category) {
                $query->whereIn('category_id', $category->descendantIds());
            }
        }

        foreach (['is_featured', 'is_popular', 'is_new', 'is_coming_soon', 'is_active'] as $flag) {
            $value = $request->input($flag);
            if ($value === '0' || $value === '1') {
                $query->where($flag, (int) $value);
            }
        }

        if (in_array($request->input('request_flow_type'), ['standard', 'event_project'], true)) {
            $query->where('request_flow_type', $request->input('request_flow_type'));
        }

        // Tab "Tampil di Home": batasi ke layanan yang benar-benar dirender grid home.
        if ($request->input('scope') === 'home') {
            $query->whereIn('id', $this->homeServiceIds());
        }
    }

    /**
     * ID layanan yang tampil di grid home mobile: aktif, featured diprioritaskan,
     * lalu diurutkan sort_order, diambil 7 teratas — persis logika ServiceGrid.
     */
    private function homeServiceIds(): array
    {
        return MobileService::where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(self::HOME_TILE_LIMIT)
            ->pluck('id')
            ->all();
    }

    /** Opsi kategori berindentasi (urut pohon) untuk dropdown filter. */
    private function categoryOptions(): array
    {
        $roots = Category::with('childrenRecursive')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $flatten = function ($nodes, int $depth) use (&$flatten): array {
            $out = [];
            foreach ($nodes as $node) {
                $out[] = ['id' => $node->id, 'label' => str_repeat('— ', $depth) . $node->name];
                $out = array_merge($out, $flatten($node->childrenRecursive, $depth + 1));
            }

            return $out;
        };

        return $flatten($roots, 0);
    }

    public function forms(Request $request)
    {
        $service = null;

        try {
            if ($request->filled('id_mobile_service')) {
                $service = $this->mobileServiceAdminService->find((int) $request->id_mobile_service);
            }

            $categoryTree = \App\Models\Category::orderBy('sort_order')->orderBy('name')->get(['id', 'parent_id', 'name']);
            $formOptions = \App\Models\Form::active()->orderBy('name')->get(['id', 'name']);
            $stepTemplateOptions = \App\Models\StepTemplate::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['id', 'name', 'is_default']);
            $priceTypes = config('form_builder.price_types');
            $priceItems = $service ? $service->priceItems()->get() : collect();

            return $this->setView('admin.components.forms.')
                ->view($request->view, compact(
                    'service', 'categoryTree', 'formOptions', 'stepTemplateOptions', 'priceTypes', 'priceItems',
                ));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /** Data pendukung form layanan. */
    private function formData($service = null): array
    {
        return [
            'service' => $service,
            'categoryTree' => \App\Models\Category::orderBy('sort_order')->orderBy('name')->get(['id', 'parent_id', 'name']),
            'formOptions' => \App\Models\Form::active()->orderBy('name')->get(['id', 'name']),
            'stepTemplateOptions' => \App\Models\StepTemplate::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['id', 'name', 'is_default']),
            'priceTypes' => config('form_builder.price_types'),
            'priceItems' => $service ? $service->priceItems()->get() : collect(),
        ];
    }

    /** Halaman tambah layanan (bukan modal). */
    public function create()
    {
        return $this->view('create', $this->formData());
    }

    /** Halaman edit layanan (bukan modal). */
    public function edit(int $id)
    {
        return $this->view('edit', $this->formData($this->mobileServiceAdminService->find($id)));
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
