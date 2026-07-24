<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'product_order_id',
        'product_order_item_id',
        'mobile_user_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    /**
     * Hitung ulang agregat rating & jumlah ulasan pada produk terkait, lalu
     * simpan ke kolom `products.rating` / `products.review_count`. Dipanggil
     * setiap ada review baru supaya kartu & detail produk tak perlu meng-avg
     * saat query.
     */
    public static function syncProductAggregate(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $stats = static::query()
            ->where('product_id', $productId)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating')
            ->first();

        $product->forceFill([
            'review_count' => (int) ($stats->total ?? 0),
            'rating' => round((float) ($stats->avg_rating ?? 0), 1),
        ])->save();
    }
}
