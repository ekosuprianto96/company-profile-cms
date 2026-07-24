<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PromotionAdminService
{
    public function queryForAdmin()
    {
        return Promotion::query()->orderBy('sort_order')->orderByDesc('id');
    }

    public function find(int $id): Promotion
    {
        return Promotion::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $banner = null, ?UploadedFile $cover = null): Promotion
    {
        return DB::transaction(function () use ($data, $banner, $cover) {
            $data['slug'] = $this->uniqueSlug($data['title']);

            if ($banner) {
                $data['banner_image'] = $this->storeImage($banner);
            }
            if ($cover) {
                $data['cover_image'] = $this->storeImage($cover);
            }

            return Promotion::create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data, ?UploadedFile $banner = null, ?UploadedFile $cover = null): Promotion
    {
        return DB::transaction(function () use ($id, $data, $banner, $cover) {
            $promotion = $this->find($id);

            if (($data['title'] ?? null) && $data['title'] !== $promotion->title) {
                $data['slug'] = $this->uniqueSlug($data['title'], $promotion->id);
            }

            // Gambar lama dibuang hanya bila benar-benar diganti.
            if ($banner) {
                $this->removeImage($promotion->banner_image);
                $data['banner_image'] = $this->storeImage($banner);
            }
            if ($cover) {
                $this->removeImage($promotion->cover_image);
                $data['cover_image'] = $this->storeImage($cover);
            }

            $promotion->update($data);

            return $promotion->refresh();
        });
    }

    public function delete(int $id): bool
    {
        $promotion = $this->find($id);
        $this->removeImage($promotion->banner_image);
        $this->removeImage($promotion->cover_image);

        return (bool) $promotion->delete();
    }

    protected function storeImage(UploadedFile $image): string
    {
        return $image->store('promotions', 'public');
    }

    protected function removeImage(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base ?: 'promosi';
        $suffix = 1;

        while (
            Promotion::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }
}
