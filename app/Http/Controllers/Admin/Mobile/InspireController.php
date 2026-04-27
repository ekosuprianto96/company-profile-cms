<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\InspirePostStoreRequest;
use App\Http\Requests\InspirePostUpdateRequest;
use App\Services\InspirePostService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class InspireController extends Controller
{
    use AdminView;

    public function __construct(
        protected InspirePostService $inspirePostService
    ) {
        $this->setView('admin.pages.mobile.inspirasi');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function create()
    {
        return $this->view('create');
    }

    public function store(InspirePostStoreRequest $request)
    {
        try {
            $post = $this->inspirePostService->create($request);

            Alert::success(
                'Sukses',
                'Konten inspire berhasil disimpan dengan status: ' . ($post->is_published ? Status::PUBLISH->text() : Status::UNPUBLISH->text())
            );

            return redirect()->route('admin.mobile.inspirasi.index');
        } catch (\Throwable $th) {
            Alert::error('Gagal!', $th->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function edit(string $slug)
    {
        return $this->view('edit', [
            'inspire' => $this->inspirePostService->findBySlug($slug),
        ]);
    }

    public function update(InspirePostUpdateRequest $request, string $slug)
    {
        try {
            $this->inspirePostService->update($request, $slug);

            Alert::success('Sukses', 'Konten inspire berhasil diperbarui.');

            return redirect()->route('admin.mobile.inspirasi.index');
        } catch (\Throwable $th) {
            Alert::error('Gagal!', $th->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->inspirePostService->delete((string) $request->slug);

            return response()->json([
                'message' => 'Konten inspire berhasil dihapus.',
                'redirect_url' => route('admin.mobile.inspirasi.index'),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function data()
    {
        return DataTables::of($this->inspirePostService->all())
            ->addColumn('thumbnail', function ($item) {
                if (! $item->thumbnail) {
                    return '<span class="text-muted">-</span>';
                }

                return '<img src="' . e($item->cover_image_url) . '" alt="' . e($item->title) . '" style="width: 72px; height: 48px; object-fit: cover; border-radius: 10px;">';
            })
            ->addColumn('title', fn ($item) => '<div><strong>' . e($item->title) . '</strong><div class="text-muted small">' . e($item->summary ?? '-') . '</div></div>')
            ->addColumn('category', fn ($item) => '<span class="badge badge-outline-primary rounded badge-sm">' . e($item->category) . '</span>')
            ->addColumn('featured', fn ($item) => '<span class="badge badge-sm badge-' . ($item->is_featured ? 'success' : 'secondary') . '">' . ($item->is_featured ? 'Featured' : 'Regular') . '</span>')
            ->addColumn('status', fn ($item) => '<span class="badge badge-sm badge-' . ($item->is_published ? 'success' : 'danger') . '">' . ($item->is_published ? Status::PUBLISH->text() : Status::UNPUBLISH->text()) . '</span>')
            ->addColumn('meta', fn ($item) => '<div class="small"><div><strong>Read:</strong> ' . (int) $item->reading_time . ' menit</div><div><strong>Urutan:</strong> ' . (int) $item->sort_order . '</div></div>')
            ->addColumn('created_by', fn ($item) => $item->createdBy?->account?->nama_lengkap ?? '-')
            ->addColumn('updated_by', fn ($item) => $item->updatedBy?->account?->nama_lengkap ?? '-')
            ->addColumn('action', function ($item) {
                return '
                    <div class="d-flex justify-content-center align-items-center" style="gap: 10px">
                        <a href="' . route('admin.mobile.inspirasi.edit', $item->slug) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                        <a href="javascript:void(0)" onclick="deleteInspirePost(\'' . e($item->slug) . '\')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-line"></i></a>
                    </div>
                ';
            })
            ->rawColumns(['thumbnail', 'title', 'category', 'featured', 'status', 'meta', 'created_by', 'updated_by', 'action'])
            ->make(true);
    }
}
