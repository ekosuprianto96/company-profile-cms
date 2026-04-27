<?php

namespace App\Services;

use App\Models\MobileBudgetOption;
use App\Repositories\MobileBudgetOptionRepository;
use Illuminate\Support\Str;

class MobileBudgetOptionAdminService
{
    public function __construct(
        protected MobileBudgetOptionRepository $mobileBudgetOptionRepository
    ) {}

    public function queryForAdmin()
    {
        return $this->mobileBudgetOptionRepository->queryForAdmin();
    }

    public function listActive()
    {
        return $this->mobileBudgetOptionRepository->listActive();
    }

    public function find(int $id): MobileBudgetOption
    {
        $budgetOption = $this->mobileBudgetOptionRepository->find($id);
        if (!$budgetOption) {
            throw new \Exception('Pilihan anggaran tidak ditemukan.', 404);
        }

        return $budgetOption;
    }

    public function create(array $payload): MobileBudgetOption
    {
        return $this->mobileBudgetOptionRepository->store($this->normalizePayload($payload));
    }

    public function update(int $id, array $payload): MobileBudgetOption
    {
        $existing = $this->find($id);

        return $this->mobileBudgetOptionRepository->updateById(
            $id,
            $this->normalizePayload($payload, $existing)
        );
    }

    public function delete(int $id): bool
    {
        return $this->mobileBudgetOptionRepository->deleteById($id);
    }

    private function normalizePayload(array $payload, ?MobileBudgetOption $existing = null): array
    {
        $name = trim((string) ($payload['name'] ?? ($existing->name ?? '')));
        if ($name === '') {
            throw new \Exception('Nama pilihan anggaran wajib diisi.', 422);
        }

        $minAmount = $payload['min_amount'] ?? null;
        $maxAmount = $payload['max_amount'] ?? null;
        $minAmount = $minAmount === '' ? null : $minAmount;
        $maxAmount = $maxAmount === '' ? null : $maxAmount;

        if ($minAmount !== null) {
            $minAmount = (int) $minAmount;
        }

        if ($maxAmount !== null) {
            $maxAmount = (int) $maxAmount;
        }

        if ($minAmount !== null && $maxAmount !== null && $minAmount > $maxAmount) {
            throw new \Exception('Nilai minimum tidak boleh lebih besar dari maksimum.', 422);
        }

        return [
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name, $existing?->id),
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
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
            $existing = $this->mobileBudgetOptionRepository->findBySlug($slug);
            if (!$existing || ($ignoreId && (int) $existing->id === $ignoreId)) {
                return $slug;
            }

            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
    }
}

