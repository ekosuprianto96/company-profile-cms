<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;

class ProductReviewAdminService
{
    /**
     * Daftar penilaian untuk admin, bisa disaring per produk (?product_id).
     * Diurut terbaru.
     */
    public function queryForAdmin(?int $productId = null)
    {
        return ProductReview::query()
            ->with(['product:id,name,slug,primary_image', 'user:id,name'])
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->orderByDesc('id');
    }

    public function find(int $id): ProductReview
    {
        return ProductReview::query()
            ->with(['product:id,name,slug,primary_image,rating,review_count', 'user:id,name', 'order:id,order_number'])
            ->findOrFail($id);
    }

    /** Produk yang punya minimal 1 ulasan — untuk dropdown filter. */
    public function reviewedProducts()
    {
        return Product::query()
            ->where('review_count', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'rating', 'review_count']);
    }
}
