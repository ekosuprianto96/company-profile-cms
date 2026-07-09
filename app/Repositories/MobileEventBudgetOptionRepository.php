<?php

namespace App\Repositories;

use App\Models\MobileEventBudgetOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileEventBudgetOptionRepository extends BaseRepositori
{
    protected $fillable = ['name', 'slug', 'min_amount', 'max_amount', 'sort_order', 'is_active'];

    public function __construct()
    {
        $this->setModel(MobileEventBudgetOption::class);
        parent::__construct();
    }

    public function queryForAdmin(): Builder
    {
        return $this->model->with(['createdBy.account', 'updatedBy.account'])->orderBy('sort_order')->orderBy('name');
    }

    public function listActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function findBySlug(string $slug): ?MobileEventBudgetOption
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileEventBudgetOption
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileEventBudgetOption
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Pilihan anggaran event tidak ditemukan.', 404);
        }

        $item->update($attributes);
        return $item->fresh();
    }

    public function deleteById(int $id): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Pilihan anggaran event tidak ditemukan.', 404);
        }

        return (bool) $item->delete();
    }
}
