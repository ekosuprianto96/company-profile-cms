<?php

namespace App\Services;

use App\Models\MobileService;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminService
{
    public function queryForAdmin()
    {
        return Product::query()->with('masterCategory.parent.parent')->orderByDesc('id');
    }

    public function find(int $id): Product
    {
        return Product::query()->with(['masterCategory.parent.parent', 'services:id'])->findOrFail($id);
    }

    public function categories()
    {
        return ProductCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function services()
    {
        return MobileService::query()->orderBy('title')->get(['id', 'title']);
    }

    public function create(array $data, array $serviceIds = [], ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $serviceIds, $image) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['sku'] = $data['sku'] ?: $this->generateSku($data['name']);
            if ($image) {
                $data['primary_image'] = $this->storeImage($image);
            }

            $product = Product::create($data);
            $this->syncServices($product, $serviceIds);

            return $product;
        });
    }

    public function update(int $id, array $data, array $serviceIds = [], ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($id, $data, $serviceIds, $image) {
            $product = $this->find($id);

            if (($data['name'] ?? null) && $data['name'] !== $product->name) {
                $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            }
            if ($image) {
                $this->removeImage($product->primary_image);
                $data['primary_image'] = $this->storeImage($image);
            }

            $product->update($data);
            $this->syncServices($product, $serviceIds);

            return $product->fresh(['category', 'services']);
        });
    }

    public function delete(int $id): bool
    {
        $product = $this->find($id);
        $this->removeImage($product->primary_image);

        return (bool) $product->delete();
    }

    protected function syncServices(Product $product, array $serviceIds): void
    {
        if ($product->service_scope === 'specific') {
            $product->services()->sync(array_unique(array_filter($serviceIds)));
        } else {
            $product->services()->detach();
        }
    }

    protected function storeImage(UploadedFile $image): string
    {
        return $image->store('products', 'public');
    }

    protected function removeImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'produk';
        $i = 1;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    protected function generateSku(string $name): string
    {
        return strtoupper(Str::slug(Str::limit($name, 6, ''))) . '-' . strtoupper(Str::random(4));
    }
}
