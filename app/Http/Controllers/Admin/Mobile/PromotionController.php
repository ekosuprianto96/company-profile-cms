<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\PromotionAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected PromotionAdminService $service
    ) {
        $this->setView('admin.pages.mobile.promotions');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('promotion', function ($promotion) {
                    $img = $promotion->banner_image
                        ? '<img src="' . e(Storage::disk('public')->url($promotion->banner_image)) . '" style="width:64px;height:34px;object-fit:cover;border-radius:6px;">'
                        : '<span class="d-inline-flex align-items-center justify-content-center bg-light text-muted" style="width:64px;height:34px;border-radius:6px;"><i class="ri-image-line"></i></span>';

                    return '<div class="d-flex align-items-center" style="gap:10px">' . $img . '
                        <div class="d-flex flex-column"><span class="fw-semibold">' . e($promotion->title) . '</span><small class="text-muted">' . e($promotion->summary ?: $promotion->slug) . '</small></div></div>';
                })
                ->addColumn('placement', fn ($promotion) => $promotion->placement === \App\Models\Promotion::PLACEMENT_HERO
                    ? '<span class="badge badge-info badge-sm">Slider Utama</span>'
                    : '<span class="badge badge-light badge-sm">Section Promosi</span>')
                ->addColumn('period', function ($promotion) {
                    $start = $promotion->starts_at?->format('d M Y') ?? '—';
                    $end = $promotion->ends_at?->format('d M Y') ?? 'Tanpa batas';

                    return '<small>' . e($start) . ' → ' . e($end) . '</small>';
                })
                ->addColumn('status', function ($promotion) {
                    if (! $promotion->is_active) {
                        return '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                    }

                    return $promotion->isRunning()
                        ? '<span class="badge badge-success badge-sm">Tayang</span>'
                        : '<span class="badge badge-warning badge-sm">Di luar periode</span>';
                })
                ->addColumn('action', fn ($promotion) => '<div class="d-flex w-full justify-content-center align-items-center" style="gap:10px">
                        <a href="javascript:void(0)" data-bind-promotion="' . $promotion->id . '" class="btn btn-success btn-xs editPromotion" title="Edit"><i class="ri-pencil-line"></i></a>
                        <a href="javascript:void(0)" onclick="deletePromotion(' . $promotion->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>')
                ->rawColumns(['promotion', 'placement', 'period', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $promotion = null;

        try {
            if ($request->filled('id_promotion')) {
                $promotion = $this->service->find((int) $request->id_promotion);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('promotion'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            [$data, $banner, $cover] = $this->validatePayload($request);
            $this->service->create($data, $banner, $cover);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan promosi.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            [$data, $banner, $cover] = $this->validatePayload($request);
            $this->service->update($id, $data, $banner, $cover);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah promosi.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_promotion);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus promosi.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: \Illuminate\Http\UploadedFile|null, 2: \Illuminate\Http\UploadedFile|null}
     */
    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'placement' => ['required', 'in:hero,promo'],
            'summary' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'banner_image' => ['nullable', 'image', 'max:4096'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = collect($validated)->except(['banner_image', 'cover_image'])->toArray();
        $data['sort_order'] = $validated['sort_order'] ?? 0;

        return [$data, $request->file('banner_image'), $request->file('cover_image')];
    }
}
