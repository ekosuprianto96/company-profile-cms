<?php

namespace App\Services;

use App\Models\MobileEventBudgetOption;
use App\Models\MobileEventPackage;
use App\Models\MobileEventProjectNeed;
use App\Models\MobileEventProjectType;
use App\Repositories\MobileEventBudgetOptionRepository;
use App\Repositories\MobileEventPackageRepository;
use App\Repositories\MobileEventProjectNeedRepository;
use App\Repositories\MobileEventProjectTypeRepository;
use Illuminate\Support\Str;

class MobileEventProjectAdminService
{
    public function __construct(
        protected MobileEventProjectTypeRepository $typeRepository,
        protected MobileEventProjectNeedRepository $needRepository,
        protected MobileEventPackageRepository $packageRepository,
        protected MobileEventBudgetOptionRepository $budgetRepository,
    ) {}

    public function queryTypes()
    {
        return $this->typeRepository->queryForAdmin();
    }

    public function queryNeeds()
    {
        return $this->needRepository->queryForAdmin();
    }

    public function queryPackages()
    {
        return $this->packageRepository->queryForAdmin();
    }

    public function queryBudgets()
    {
        return $this->budgetRepository->queryForAdmin();
    }

    public function activeTypes()
    {
        return $this->typeRepository->listActive();
    }

    public function activeNeeds()
    {
        return $this->needRepository->listActive();
    }

    public function findType(int $id): MobileEventProjectType
    {
        $item = $this->typeRepository->find($id);
        if (!$item) {
            throw new \Exception('Jenis project event tidak ditemukan.', 404);
        }

        return $item;
    }

    public function findNeed(int $id): MobileEventProjectNeed
    {
        $item = $this->needRepository->find($id);
        if (!$item) {
            throw new \Exception('Kebutuhan project event tidak ditemukan.', 404);
        }

        return $item;
    }

    public function findPackage(int $id): MobileEventPackage
    {
        $item = $this->packageRepository->find($id);
        if (!$item) {
            throw new \Exception('Paket event tidak ditemukan.', 404);
        }

        return $item;
    }

    public function findBudget(int $id): MobileEventBudgetOption
    {
        $item = $this->budgetRepository->find($id);
        if (!$item) {
            throw new \Exception('Pilihan anggaran event tidak ditemukan.', 404);
        }

        return $item;
    }

    public function createType(array $payload): MobileEventProjectType
    {
        return $this->typeRepository->store($this->normalizeTypePayload($payload));
    }

    public function updateType(int $id, array $payload): MobileEventProjectType
    {
        return $this->typeRepository->updateById($id, $this->normalizeTypePayload($payload, $this->findType($id)));
    }

    public function deleteType(int $id): bool
    {
        return $this->typeRepository->deleteById($id);
    }

    public function createNeed(array $payload): MobileEventProjectNeed
    {
        return $this->needRepository->store($this->normalizeNeedPayload($payload));
    }

    public function updateNeed(int $id, array $payload): MobileEventProjectNeed
    {
        return $this->needRepository->updateById($id, $this->normalizeNeedPayload($payload, $this->findNeed($id)));
    }

    public function deleteNeed(int $id): bool
    {
        return $this->needRepository->deleteById($id);
    }

    public function createPackage(array $payload): MobileEventPackage
    {
        return $this->packageRepository->store($this->normalizePackagePayload($payload));
    }

    public function updatePackage(int $id, array $payload): MobileEventPackage
    {
        return $this->packageRepository->updateById($id, $this->normalizePackagePayload($payload, $this->findPackage($id)));
    }

    public function deletePackage(int $id): bool
    {
        return $this->packageRepository->deleteById($id);
    }

    public function createBudget(array $payload): MobileEventBudgetOption
    {
        return $this->budgetRepository->store($this->normalizeBudgetPayload($payload));
    }

    public function updateBudget(int $id, array $payload): MobileEventBudgetOption
    {
        return $this->budgetRepository->updateById($id, $this->normalizeBudgetPayload($payload, $this->findBudget($id)));
    }

    public function deleteBudget(int $id): bool
    {
        return $this->budgetRepository->deleteById($id);
    }

    private function normalizeTypePayload(array $payload, ?MobileEventProjectType $existing = null): array
    {
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($name === '') {
            throw new \Exception('Nama jenis project event wajib diisi.', 422);
        }

        return [
            'name' => $name,
            'slug' => $this->uniqueTypeSlug($name, $existing?->id),
            'description' => $payload['description'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function normalizeNeedPayload(array $payload, ?MobileEventProjectNeed $existing = null): array
    {
        $projectTypeId = (int) ($payload['mobile_event_project_type_id'] ?? $existing?->mobile_event_project_type_id ?? 0);
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($projectTypeId <= 0 || $name === '') {
            throw new \Exception('Jenis project dan nama kebutuhan event wajib diisi.', 422);
        }

        return [
            'mobile_event_project_type_id' => $projectTypeId,
            'name' => $name,
            'slug' => $this->uniqueNeedSlug($projectTypeId, $name, $existing?->id),
            'description' => $payload['description'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function normalizePackagePayload(array $payload, ?MobileEventPackage $existing = null): array
    {
        $projectNeedId = (int) ($payload['mobile_event_project_need_id'] ?? $existing?->mobile_event_project_need_id ?? 0);
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($projectNeedId <= 0 || $name === '') {
            throw new \Exception('Kebutuhan project dan nama paket event wajib diisi.', 422);
        }

        return [
            'mobile_event_project_need_id' => $projectNeedId,
            'name' => $name,
            'slug' => $this->uniquePackageSlug($projectNeedId, $name, $existing?->id),
            'description' => $payload['description'] ?? null,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function normalizeBudgetPayload(array $payload, ?MobileEventBudgetOption $existing = null): array
    {
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($name === '') {
            throw new \Exception('Nama pilihan anggaran event wajib diisi.', 422);
        }

        $minAmount = $payload['min_amount'] ?? null;
        $maxAmount = $payload['max_amount'] ?? null;
        $minAmount = $minAmount === '' ? null : $minAmount;
        $maxAmount = $maxAmount === '' ? null : $maxAmount;
        $minAmount = $minAmount !== null ? (int) $minAmount : null;
        $maxAmount = $maxAmount !== null ? (int) $maxAmount : null;

        if ($minAmount !== null && $maxAmount !== null && $minAmount > $maxAmount) {
            throw new \Exception('Nilai minimum tidak boleh lebih besar dari maksimum.', 422);
        }

        return [
            'name' => $name,
            'slug' => $this->uniqueBudgetSlug($name, $existing?->id),
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ];
    }

    private function uniqueTypeSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($existing = $this->typeRepository->findBySlug($slug)) {
            if ($ignoreId && (int) $existing->id === $ignoreId) {
                return $slug;
            }
            $slug = "{$baseSlug}-" . ++$counter;
        }

        return $slug;
    }

    private function uniqueNeedSlug(int $projectTypeId, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($existing = $this->needRepository->findBySlug($projectTypeId, $slug)) {
            if ($ignoreId && (int) $existing->id === $ignoreId) {
                return $slug;
            }
            $slug = "{$baseSlug}-" . ++$counter;
        }

        return $slug;
    }

    private function uniquePackageSlug(int $projectNeedId, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($existing = $this->packageRepository->findBySlug($projectNeedId, $slug)) {
            if ($ignoreId && (int) $existing->id === $ignoreId) {
                return $slug;
            }
            $slug = "{$baseSlug}-" . ++$counter;
        }

        return $slug;
    }

    private function uniqueBudgetSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($existing = $this->budgetRepository->findBySlug($slug)) {
            if ($ignoreId && (int) $existing->id === $ignoreId) {
                return $slug;
            }
            $slug = "{$baseSlug}-" . ++$counter;
        }

        return $slug;
    }
}
