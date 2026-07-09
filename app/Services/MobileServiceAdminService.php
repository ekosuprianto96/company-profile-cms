<?php

namespace App\Services;

use App\Models\MobileService;
use App\Repositories\MobileServiceRepository;
use Illuminate\Support\Str;

class MobileServiceAdminService
{
    public function __construct(
        protected MobileServiceRepository $mobileServiceRepository
    ) {}

    public function queryForAdmin()
    {
        return $this->mobileServiceRepository->queryForAdmin();
    }

    public function find(int $id): MobileService
    {
        $service = $this->mobileServiceRepository->find($id);
        if (!$service) {
            throw new \Exception('Layanan mobile tidak ditemukan.', 404);
        }

        return $service;
    }

    public function create(array $payload): MobileService
    {
        $service = $this->mobileServiceRepository->store($this->normalizePayload($payload));
        $service->needTypes()->sync($this->resolveNeedTypeIds($payload));

        return $service->fresh(['needTypes']);
    }

    public function update(int $id, array $payload): MobileService
    {
        $existing = $this->find($id);
        $normalized = $this->normalizePayload($payload, $existing);

        $updated = $this->mobileServiceRepository->updateById($id, $normalized);
        $updated->needTypes()->sync($this->resolveNeedTypeIds($payload));

        if (($existing->icon_type === 'image') && ($updated->icon_type !== 'image') && !empty($existing->icon_image)) {
            $this->removeImage($existing->icon_image);
        }

        if (!empty($existing->icon_image) && $existing->icon_image !== $updated->icon_image) {
            $this->removeImage($existing->icon_image);
        }

        if (!empty($existing->cover_image) && $existing->cover_image !== $updated->cover_image) {
            $this->removeImage($existing->cover_image);
        }

        return $updated->fresh(['needTypes']);
    }

    public function delete(int $id): bool
    {
        $service = $this->find($id);

        if (!empty($service->icon_image)) {
            $this->removeImage($service->icon_image);
        }

        if (!empty($service->cover_image)) {
            $this->removeImage($service->cover_image);
        }

        return $this->mobileServiceRepository->deleteById($id);
    }

    private function normalizePayload(array $payload, ?MobileService $existing = null): array
    {
        $title = trim((string) ($payload['title'] ?? ($existing->title ?? '')));
        if ($title === '') {
            throw new \Exception('Judul layanan mobile wajib diisi.', 422);
        }

        $slug = $this->generateUniqueSlug($title, $existing?->id);
        $iconType = (string) ($payload['icon_type'] ?? $existing?->icon_type ?? 'icon');

        $iconImage = $payload['icon_image'] ?? null;
        $iconImagePath = $payload['icon_image_path'] ?? null;
        $coverImage = $payload['cover_image'] ?? null;
        $coverImagePath = $payload['cover_image_path'] ?? null;

        if ($existing && empty($iconImage)) {
            $iconImage = $iconImagePath === '' ? null : $existing->icon_image;
        }

        if ($existing && empty($coverImage)) {
            $coverImage = $coverImagePath === '' ? null : $existing->cover_image;
        }

        $iconImage = $this->moveTempImage($iconImage);
        $coverImage = $this->moveTempImage($coverImage);

        return [
            'title' => $title,
            'slug' => $slug,
            'request_flow_type' => in_array(($payload['request_flow_type'] ?? 'standard'), ['standard', 'event_project'], true)
                ? $payload['request_flow_type']
                : 'standard',
            'summary' => $payload['summary'] ?? null,
            'description' => $payload['description'] ?? null,
            'icon_type' => $iconType,
            'icon' => $iconType === 'icon'
                ? trim((string) ($payload['icon'] ?? ($existing->icon ?? 'home-repair-service')))
                : null,
            'icon_image' => $iconType === 'image' ? $iconImage : null,
            'cover_image' => $coverImage,
            'card_color' => $payload['card_color'] ?? '#6ec7d0',
            'text_color' => $payload['text_color'] ?? '#0e4751',
            'badge_text' => $payload['badge_text'] ?? null,
            'price_label' => $payload['price_label'] ?? null,
            'rating' => $payload['rating'] ?? null,
            'projects_label' => $payload['projects_label'] ?? null,
            'estimated_duration' => $payload['estimated_duration'] ?? null,
            'cta_text' => $payload['cta_text'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_new' => (bool) ($payload['is_new'] ?? false),
            'is_featured' => (bool) ($payload['is_featured'] ?? true),
            'is_popular' => (bool) ($payload['is_popular'] ?? true),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $existing = $this->mobileServiceRepository->findBySlug($slug);

            if (!$existing || ($ignoreId && (int) $existing->id === $ignoreId)) {
                return $slug;
            }

            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
    }

    private function moveTempImage(?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $tempPath = 'assets/images/temps/' . $filename;
        $destinationPath = 'assets/images/mobile-services/' . $filename;

        if (!is_dir(public_path('assets/images/mobile-services/'))) {
            mkdir(public_path('assets/images/mobile-services/'), 0777, true);
        }

        if (file_exists(public_path($tempPath))) {
            rename(public_path($tempPath), public_path($destinationPath));
        }

        return $filename;
    }

    private function removeImage(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $path = public_path('assets/images/mobile-services/' . $filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function resolveNeedTypeIds(array $payload): array
    {
        return collect($payload['need_types'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->toArray();
    }
}
