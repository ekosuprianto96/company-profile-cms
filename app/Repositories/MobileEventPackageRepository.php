<?php

namespace App\Repositories;

use App\Models\MobileEventPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileEventPackageRepository extends BaseRepositori
{
    protected $fillable = ['mobile_event_project_need_id', 'name', 'slug', 'description', 'sort_order', 'is_active'];

    public function __construct()
    {
        $this->setModel(MobileEventPackage::class);
        parent::__construct();
    }

    public function queryForAdmin(): Builder
    {
        return $this->model->with(['projectNeed.projectType', 'createdBy.account', 'updatedBy.account'])->orderBy('mobile_event_project_need_id')->orderBy('sort_order')->orderBy('name');
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->whereHas('projectNeed', fn ($query) => $query->where('is_active', true)->whereHas('projectType', fn ($typeQuery) => $typeQuery->where('is_active', true)))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(int $projectNeedId, string $slug): ?MobileEventPackage
    {
        return $this->model->where('mobile_event_project_need_id', $projectNeedId)->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileEventPackage
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileEventPackage
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Paket event tidak ditemukan.', 404);
        }

        $item->update($attributes);
        return $item->fresh();
    }

    public function deleteById(int $id): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Paket event tidak ditemukan.', 404);
        }

        return (bool) $item->delete();
    }
}
