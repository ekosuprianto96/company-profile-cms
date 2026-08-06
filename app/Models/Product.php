<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'slug',
        'product_category_id',
        'category_id',
        'supplier_id',
        'name',
        'brand',
        'short_description',
        'description',
        'price',
        'compare_at_price',
        'weight_grams',
        'stock',
        'rating',
        'sold_count',
        'primary_image',
        'can_be_bundled',
        'service_scope',
        'shipping_method',
        'internal_shipping_fee',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'weight_grams' => 'integer',
            'stock' => 'integer',
            'sold_count' => 'integer',
            'internal_shipping_fee' => 'integer',
            'rating' => 'decimal:1',
            'can_be_bundled' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /** Suplier produk (internal — untuk tracking di dashboard admin). */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /** Kategori master bertingkat (bersama layanan) — beda dari product_category lama. */
    public function masterCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(MobileService::class, 'product_service', 'product_id', 'mobile_service_id')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function requiresThirdPartyCourier(): bool
    {
        return $this->shipping_method === 'courier';
    }
}
