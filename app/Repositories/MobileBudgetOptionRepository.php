<?php

namespace App\Repositories;

use App\Models\MobileBudgetOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileBudgetOptionRepository extends BaseRepositori
{
    protected $fillable = [
        'name',
        'slug',
        'min_amount',
        'max_amount',
        'sort_order',
        'is_active',
    ];

    public function __construct()
    {
        $this->setModel(MobileBudgetOption::class);
        parent::__construct();
    }

    public function queryForAdmin(): Builder
    {
        return $this->model
            ->with(['createdBy.account', 'updatedBy.account'])
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?MobileBudgetOption
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileBudgetOption
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileBudgetOption
    {
        $budgetOption = $this->model->find($id);
        if (!$budgetOption) {
            throw new \Exception('Pilihan anggaran tidak ditemukan.', 404);
        }

        $budgetOption->update($attributes);
        return $budgetOption->fresh();
    }

    public function deleteById(int $id): bool
    {
        $budgetOption = $this->model->find($id);
        if (!$budgetOption) {
            throw new \Exception('Pilihan anggaran tidak ditemukan.', 404);
        }

        return (bool) $budgetOption->delete();
    }
}

