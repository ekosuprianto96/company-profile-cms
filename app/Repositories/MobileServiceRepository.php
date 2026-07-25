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
        'request_flow_type',
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
            ->with(['createdBy.account', 'updatedBy.account'])
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->with([
                'category.parent.parent', // rantai kategori untuk resolusi induk (maks. 3 tingkat)
                'priceItems',
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
