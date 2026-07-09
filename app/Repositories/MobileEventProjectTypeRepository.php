<?php

namespace App\Repositories;

use App\Models\MobileEventProjectType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MobileEventProjectTypeRepository extends BaseRepositori
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];

    public function __construct()
    {
        $this->setModel(MobileEventProjectType::class);
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

    public function findBySlug(string $slug): ?MobileEventProjectType
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileEventProjectType
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileEventProjectType
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Jenis project event tidak ditemukan.', 404);
        }

        $item->update($attributes);
        return $item->fresh();
    }

    public function deleteById(int $id): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            throw new \Exception('Jenis project event tidak ditemukan.', 404);
        }

        return (bool) $item->delete();
    }
}
