<?php

namespace App\Repositories;

use App\Models\MobileEventProjectNeed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileEventProjectNeedRepository extends BaseRepositori
{
    protected $fillable = ['mobile_event_project_type_id', 'name', 'slug', 'description', 'sort_order', 'is_active'];

    public function __construct()
    {
        $this->setModel(MobileEventProjectNeed::class);
        parent::__construct();
    }

    public function queryForAdmin(): Builder
    {
        return $this->model->with(['projectType', 'createdBy.account', 'updatedBy.account'])->orderBy('mobile_event_project_type_id')->orderBy('sort_order')->orderBy('name');
    }

    public function listActive(): Collection
    {
        return $this->model->where('is_active', true)->whereHas('projectType', fn ($query) => $query->where('is_active', true))->orderBy('sort_order')->orderBy('name')->get();
    }

    public function findBySlug(int $projectTypeId, string $slug): ?MobileEventProjectNeed
    {
        return $this->model->where('mobile_event_project_type_id', $projectTypeId)->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileEventProjectNeed
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileEventProjectNeed
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Kebutuhan project event tidak ditemukan.', 404);
        }

        $item->update($attributes);
        return $item->fresh();
    }

    public function deleteById(int $id): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Kebutuhan project event tidak ditemukan.', 404);
        }

        return (bool) $item->delete();
    }
}
