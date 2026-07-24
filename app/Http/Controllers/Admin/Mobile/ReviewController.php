<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\ProductReviewAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProductReviewAdminService $service
    ) {
        $this->setView('admin.pages.mobile.reviews');
    }

    public function index()
    {
        $products = $this->service->reviewedProducts();

        return $this->view('index', compact('products'));
    }

    public function data(Request $request)
    {
        try {
            $productId = $request->filled('product_id') ? (int) $request->product_id : null;

            return DataTables::of($this->service->queryForAdmin($productId))
                ->addColumn('product', fn ($r) => '<div class="d-flex flex-column"><span class="fw-semibold">' . e($r->product?->name ?? '-') . '</span><small class="text-muted">' . e(optional($r->user)->name ?? 'Pengguna') . '</small></div>')
                ->addColumn('rating', fn ($r) => $this->starsHtml((int) $r->rating))
                ->addColumn('comment', fn ($r) => $r->comment
                    ? '<span class="text-muted">' . e(\Illuminate\Support\Str::limit($r->comment, 60)) . '</span>'
                    : '<span class="text-muted fst-italic">Tanpa komentar</span>')
                ->addColumn('date', fn ($r) => optional($r->created_at)->format('d M Y'))
                ->addColumn('action', fn ($r) => '<a href="javascript:void(0)" data-bind-review="' . $r->id . '" class="btn btn-primary btn-xs detailReview" title="Detail"><i class="ri-eye-line"></i></a>')
                ->rawColumns(['product', 'rating', 'comment', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        try {
            $review = $this->service->find((int) $request->id_review);

            return $this->setView('admin.components.forms.')->view($request->view, compact('review'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /** Bintang emas terisi + abu kosong, untuk kolom tabel & detail. */
    protected function starsHtml(int $rating): string
    {
        $html = '<span class="text-nowrap" style="letter-spacing:1px">';
        for ($i = 1; $i <= 5; $i++) {
            $color = $i <= $rating ? '#c8915c' : '#d9d9d9';
            $html .= '<i class="ri-star-fill" style="color:' . $color . '"></i>';
        }

        return $html . '</span>';
    }
}
