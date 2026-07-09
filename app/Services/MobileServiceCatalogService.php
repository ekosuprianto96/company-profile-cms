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
            ->map(function ($service) {
                $iconImage = ($service->icon_type === 'image' && !empty($service->icon_image))
                    ? image_url('mobile-services', (string) $service->icon_image)
                    : null;

                $coverImage = !empty($service->cover_image)
                    ? image_url('mobile-services', (string) $service->cover_image)
                    : null;

                return [
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
                    'need_types' => $service->needTypes
                        ->map(fn ($needType) => [
                            'id' => $needType->id,
                            'name' => $needType->name,
                            'slug' => $needType->slug,
                            'description' => $needType->description,
                            'sort_order' => (int) $needType->sort_order,
                        ])
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }
}
