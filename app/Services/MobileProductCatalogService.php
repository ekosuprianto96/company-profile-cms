<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class MobileProductCatalogService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->with('category')->where('is_active', true);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $filters['category'])->orWhere('id', $filters['category']));
        }
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('brand', 'like', "%{$q}%"));
        }
        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', (int) $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (int) $filters['max_price']);
        }
        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }
        if (! empty($filters['service_id'])) {
            $serviceId = (int) $filters['service_id'];
            $query->where(fn ($w) => $w->where('service_scope', 'all')
                ->orWhereHas('services', fn ($s) => $s->where('mobile_services.id', $serviceId)));
        }
        // Batasi ke cakupan voucher (untuk tombol "Pakai"). item_ids = null → semua.
        if (array_key_exists('item_ids', $filters) && is_array($filters['item_ids'])) {
            $query->whereIn('id', $filters['item_ids']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('sold_count'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('id'),
        };

        return $query->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::query()->with(['category', 'images', 'services:id,title'])->where('slug', $slug)->where('is_active', true)->first();
    }

    public function categories()
    {
        return ProductCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')
            ->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
            ])->all();
    }

    public function listItem(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand,
            'category' => $product->category?->name,
            'price' => (int) $product->price,
            'compare_at_price' => $product->compare_at_price ? (int) $product->compare_at_price : null,
            'discount_percent' => $this->discountPercent($product),
            'primary_image' => storageUrl($product->primary_image),
            'rating' => (float) $product->rating,
            'review_count' => (int) $product->review_count,
            'sold_count' => (int) $product->sold_count,
            'stock' => (int) $product->stock,
            'is_featured' => (bool) $product->is_featured,
        ];
    }

    public function detail(Product $product): array
    {
        return array_merge($this->listItem($product), [
            'short_description' => $product->short_description,
            'description' => $product->description,
            'weight_grams' => (int) $product->weight_grams,
            'can_be_bundled' => (bool) $product->can_be_bundled,
            'service_scope' => $product->service_scope,
            'shipping_method' => $product->shipping_method,
            'internal_shipping_fee' => $product->internal_shipping_fee ? (int) $product->internal_shipping_fee : null,
            'images' => $product->images->map(fn ($i) => storageUrl($i->path))->filter()->values()->all(),
            'services' => $product->services->pluck('title')->all(),
        ]);
    }

    protected function discountPercent(Product $product): ?int
    {
        if (! $product->compare_at_price || $product->compare_at_price <= $product->price) {
            return null;
        }

        return (int) round((1 - $product->price / $product->compare_at_price) * 100);
    }

}
