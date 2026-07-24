<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryAdminService
{
    /**
     * Semua kategori sebagai daftar rata (flat) hasil telusur pohon, tiap baris
     * membawa `depth` untuk indentasi tampilan. Master data kecil → tanpa paginasi.
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    public function tree()
    {
        $all = Category::query()
            ->withCount('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $byParent = $all->groupBy('parent_id');
        $flat = collect();

        $walk = function ($parentId, $depth) use (&$walk, $byParent, $flat) {
            foreach ($byParent->get($parentId, collect()) as $node) {
                $node->depth = $depth;
                $flat->push($node);
                $walk($node->id, $depth + 1);
            }
        };
        $walk(null, 0);

        return $flat;
    }

    public function find(int $id): Category
    {
        return Category::query()->withCount('children')->findOrFail($id);
    }

    /**
     * Opsi induk untuk dropdown (flat + indentasi). Saat mengedit, kategori itu
     * sendiri & semua keturunannya dikecualikan agar tidak membuat lingkaran.
     *
     * @return array<int, array{id:int, label:string}>
     */
    public function parentOptions(?int $excludeId = null): array
    {
        $exclude = [];
        if ($excludeId) {
            $exclude = Category::find($excludeId)?->descendantIds() ?? [$excludeId];
        }

        return $this->tree()
            ->reject(fn ($node) => in_array($node->id, $exclude, true))
            ->map(fn ($node) => [
                'id' => $node->id,
                'label' => str_repeat('— ', $node->depth) . $node->name,
            ])
            ->values()
            ->all();
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        return Category::create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->find($id);
        $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        $category->update($data);

        return $category;
    }

    /**
     * Hapus kategori. Induk yang masih punya anak DIBLOKIR — anak harus dikosongkan
     * dulu. Anak/daun (tanpa anak) boleh dihapus.
     */
    public function delete(int $id): bool
    {
        $category = $this->find($id);

        if ($category->hasChildren()) {
            throw new \Exception('Kategori ini masih memiliki sub-kategori. Kosongkan/pindahkan sub-kategorinya dulu sebelum menghapus.', 422);
        }

        return (bool) $category->delete();
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
