<?php

namespace App\Services;

use App\Repositories\MobileServiceRepository;

class MobileServiceCatalogService
{
    public function __construct(
        protected MobileServiceRepository $mobileServiceRepository
    ) {}

    public function all(): array
    {
        return $this->mobileServiceRepository
            ->listActive()
            ->map(fn ($service) => $this->listItem($service))
            ->values()
            ->toArray();
    }

    /** Payload satu layanan (dipakai katalog & home section). */
    public function listItem($service): array
    {
                $iconImage = ($service->icon_type === 'image' && !empty($service->icon_image))
                    ? image_url('mobile-services', (string) $service->icon_image)
                    : null;

                $coverImage = !empty($service->cover_image)
                    ? image_url('mobile-services', (string) $service->cover_image)
                    : null;

                // Kategori langsung + kategori induk teratas (untuk pengelompokan di mobile).
                $category = $service->category;
                $root = $category;
                while ($root && $root->parent) {
                    $root = $root->parent;
                }

                $mapCategory = fn ($cat) => $cat ? [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => $cat->icon,
                ] : null;

                return [
                    'category' => $mapCategory($category),
                    'root_category' => $mapCategory($root),
                    'id' => $service->id,
                    'title' => $service->title,
                    'slug' => $service->slug,
                    'request_flow_type' => $service->request_flow_type ?? 'standard',
                    'summary' => $service->summary,
                    'description' => $service->description,
                    'icon' => $service->icon,
                    'icon_type' => $service->icon_type,
                    'icon_image' => $iconImage,
                    'cover_image' => $coverImage,
                    'card_color' => $service->card_color,
                    'text_color' => $service->text_color,
                    'badge_text' => $service->badge_text,
                    'price_label' => $service->price_label,
                    'rating' => $service->rating,
                    'projects_label' => $service->projects_label,
                    'estimated_duration' => $service->estimated_duration,
                    'cta_text' => $service->cta_text,
                    'sort_order' => (int) $service->sort_order,
                    'is_new' => (bool) $service->is_new,
                    'is_featured' => (bool) $service->is_featured,
                    'is_popular' => (bool) $service->is_popular,
                    'is_active' => (bool) $service->is_active,
                    'is_coming_soon' => (bool) $service->is_coming_soon,
                    'form_id' => $service->form_id,
                    'price_items' => $service->priceItems->map(fn ($item) => [
                        'type' => $item->type,
                        'label' => $item->label,
                        'amount' => (int) $item->amount,
                        'is_required' => (bool) $item->is_required,
                    ])->values()->toArray(),
                    'price_total' => (int) $service->priceItems->where('is_required', true)->sum('amount'),
                    'need_types' => [],
                ];
    }
}
