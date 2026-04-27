<?php

namespace App\Repositories;

use App\Models\MobileServiceNeedType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileServiceNeedTypeRepository extends BaseRepositori
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    public function __construct()
    {
        $this->setModel(MobileServiceNeedType::class);
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

    public function findBySlug(string $slug): ?MobileServiceNeedType
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileServiceNeedType
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileServiceNeedType
    {
        $needType = $this->model->find($id);
        if (!$needType) {
            throw new \Exception('Jenis kebutuhan layanan tidak ditemukan.', 404);
        }

        $needType->update($attributes);
        return $needType->fresh();
    }

    public function deleteById(int $id): bool
    {
        $needType = $this->model->find($id);
        if (!$needType) {
            throw new \Exception('Jenis kebutuhan layanan tidak ditemukan.', 404);
        }

        return (bool) $needType->delete();
    }
}

