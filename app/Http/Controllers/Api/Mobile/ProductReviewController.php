<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends ApiController
{
    /**
     * Kirim penilaian untuk produk-produk dalam SATU pesanan yang sudah selesai.
     * Bintang wajib per produk, komentar opsional. Aman diulang: produk yang
     * sudah dinilai dilewati (unique constraint mencegah duplikat).
     */
    public function store(Request $request, string $orderNumber)
    {
        try {
            $validated = $request->validate([
                'reviews' => ['required', 'array', 'min:1'],
                'reviews.*.product_id' => ['required', 'integer'],
                'reviews.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
                'reviews.*.comment' => ['nullable', 'string', 'max:2000'],
            ]);

            $order = ProductOrder::query()
                ->with(['items', 'reviews'])
                ->where('mobile_user_id', $request->user()->id)
                ->where('order_number', $orderNumber)
                ->first();

            if (! $order) {
                return $this->error('Pesanan tidak ditemukan.', 404);
            }

            if (! $order->isCompleted()) {
                return $this->error('Penilaian hanya bisa diberikan untuk pesanan yang sudah selesai.', 422);
            }

            // Hanya produk yang benar-benar ada di pesanan ini yang boleh dinilai.
            $orderProductIds = $order->items->pluck('product_id')->filter()->unique();
            $alreadyReviewed = $order->reviews->pluck('product_id')->all();

            $affectedProductIds = [];

            DB::transaction(function () use ($validated, $order, $request, $orderProductIds, $alreadyReviewed, &$affectedProductIds) {
                foreach ($validated['reviews'] as $entry) {
                    $productId = (int) $entry['product_id'];

                    // Lewati produk di luar pesanan atau yang sudah dinilai.
                    if (! $orderProductIds->contains($productId) || in_array($productId, $alreadyReviewed, true)) {
                        continue;
                    }

                    $item = $order->items->firstWhere('product_id', $productId);

                    ProductReview::create([
                        'product_id' => $productId,
                        'product_order_id' => $order->id,
                        'product_order_item_id' => $item?->id,
                        'mobile_user_id' => $request->user()->id,
                        'rating' => (int) $entry['rating'],
                        'comment' => isset($entry['comment']) ? trim((string) $entry['comment']) ?: null : null,
                    ]);

                    $affectedProductIds[] = $productId;
                }
            });

            // Hitung ulang agregat rating tiap produk yang baru dinilai.
            foreach (array_unique($affectedProductIds) as $productId) {
                ProductReview::syncProductAggregate($productId);
            }

            if (empty($affectedProductIds)) {
                return $this->error('Tidak ada produk baru untuk dinilai (mungkin sudah dinilai sebelumnya).', 422);
            }

            return $this->success([
                'reviewed_product_ids' => array_values(array_unique($affectedProductIds)),
            ], 'Terima kasih! Penilaianmu sudah tersimpan.');
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            $status = (int) $th->getCode();

            return $this->error($th->getMessage(), $status >= 100 && $status <= 599 ? $status : 500);
        }
    }

    /**
     * Daftar ulasan sebuah produk (untuk halaman detail produk), plus ringkasan
     * rata-rata & distribusi bintang. Paginasi sederhana lewat ?page.
     */
    public function index(Request $request, string $slug)
    {
        $product = Product::query()->where('slug', $slug)->where('is_active', true)->first();

        if (! $product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $reviews = ProductReview::query()
            ->with('user:id,name,avatar_path')
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(10);

        // Distribusi 5→1 bintang untuk bar ringkasan.
        $breakdown = ProductReview::query()
            ->where('product_id', $product->id)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return $this->success([
            'summary' => [
                'rating' => round((float) $product->rating, 1),
                'review_count' => (int) $product->review_count,
                'breakdown' => [
                    '5' => (int) ($breakdown[5] ?? 0),
                    '4' => (int) ($breakdown[4] ?? 0),
                    '3' => (int) ($breakdown[3] ?? 0),
                    '2' => (int) ($breakdown[2] ?? 0),
                    '1' => (int) ($breakdown[1] ?? 0),
                ],
            ],
            'reviews' => $reviews->map(fn (ProductReview $review) => [
                'id' => $review->id,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user?->name ?? 'Pengguna',
                'user_avatar' => storageUrl($review->user?->avatar_path),
                'created_at' => optional($review->created_at)?->toISOString(),
            ])->all(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'has_more' => $reviews->hasMorePages(),
            ],
        ], 'OK');
    }
}
