<?php

namespace App\Services;

use App\Models\MobileServiceNeedType;
use App\Repositories\MobileServiceNeedTypeRepository;
use Illuminate\Support\Str;

class MobileServiceNeedTypeAdminService
{
    public function __construct(
        protected MobileServiceNeedTypeRepository $mobileServiceNeedTypeRepository
    ) {}

    public function queryForAdmin()
    {
        return $this->mobileServiceNeedTypeRepository->queryForAdmin();
    }

    public function listActive()
    {
        return $this->mobileServiceNeedTypeRepository->listActive();
    }

    public function find(int $id): MobileServiceNeedType
    {
        $needType = $this->mobileServiceNeedTypeRepository->find($id);
        if (!$needType) {
            throw new \Exception('Jenis kebutuhan layanan tidak ditemukan.', 404);
        }

        return $needType;
    }

    public function create(array $payload): MobileServiceNeedType
    {
        return $this->mobileServiceNeedTypeRepository->store($this->normalizePayload($payload));
    }

    public function update(int $id, array $payload): MobileServiceNeedType
    {
        $existing = $this->find($id);

        return $this->mobileServiceNeedTypeRepository->updateById(
            $id,
            $this->normalizePayload($payload, $existing)
        );
    }

    public function delete(int $id): bool
    {
        return $this->mobileServiceNeedTypeRepository->deleteById($id);
    }

    private function normalizePayload(array $payload, ?MobileServiceNeedType $existing = null): array
    {
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($name === '') {
            throw new \Exception('Nama jenis kebutuhan layanan wajib diisi.', 422);
        }

        return [
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name, $existing?->id),
            'description' => $payload['description'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $existing = $this->mobileServiceNeedTypeRepository->findBySlug($slug);
            if (!$existing || ($ignoreId && (int) $existing->id === $ignoreId)) {
                return $slug;
            }

            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
    }
}

