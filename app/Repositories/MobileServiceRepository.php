<?php

namespace App\Repositories;

use App\Models\MobileService;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class MobileServiceRepository extends BaseRepositori
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'icon_type',
        'icon',
        'icon_image',
        'cover_image',
        'card_color',
        'text_color',
        'badge_text',
        'price_label',
        'rating',
        'projects_label',
        'estimated_duration',
        'cta_text',
        'sort_order',
        'is_new',
        'is_featured',
        'is_popular',
        'is_active',
    ];

    public function __construct()
    {
        $this->setModel(MobileService::class);
        parent::__construct();
    }

    public function queryForAdmin(): Builder
    {
        return $this->model
            ->with(['createdBy.account', 'updatedBy.account', 'needTypes'])
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->with([
                'needTypes' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function findBySlug(string $slug): ?MobileService
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function store(array $attributes): MobileService
    {
        return $this->model->create($attributes);
    }

    public function updateById(int $id, array $attributes): MobileService
    {
        $service = $this->model->find($id);
        if (!$service) {
            throw new \Exception('Layanan mobile tidak ditemukan.', 404);
        }

        $service->update($attributes);
        return $service->fresh();
    }

    public function deleteById(int $id): bool
    {
        $service = $this->model->find($id);
        if (!$service) {
            throw new \Exception('Layanan mobile tidak ditemukan.', 404);
        }

        return (bool) $service->delete();
    }
}
