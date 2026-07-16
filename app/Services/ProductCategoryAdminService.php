<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategoryAdminService
{
    public function queryForAdmin()
    {
        return ProductCategory::query()->withCount('products')->orderBy('sort_order')->orderBy('name');
    }

    public function find(int $id): ProductCategory
    {
        return ProductCategory::query()->findOrFail($id);
    }

    public function create(array $payload): ProductCategory
    {
        $payload['slug'] = $this->uniqueSlug($payload['name']);

        return ProductCategory::create($payload);
    }

    public function update(int $id, array $payload): ProductCategory
    {
        $category = $this->find($id);
        if (($payload['name'] ?? null) && $payload['name'] !== $category->name) {
            $payload['slug'] = $this->uniqueSlug($payload['name'], $category->id);
        }
        $category->update($payload);

        return $category->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (ProductCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
