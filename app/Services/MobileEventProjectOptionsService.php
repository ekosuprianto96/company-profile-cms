<?php

namespace App\Services;

use App\Repositories\MobileEventBudgetOptionRepository;
use App\Repositories\MobileEventPackageRepository;
use App\Repositories\MobileEventProjectNeedRepository;
use App\Repositories\MobileEventProjectTypeRepository;

class MobileEventProjectOptionsService
{
    public function __construct(
        protected MobileEventProjectTypeRepository $typeRepository,
        protected MobileEventProjectNeedRepository $needRepository,
        protected MobileEventPackageRepository $packageRepository,
        protected MobileEventBudgetOptionRepository $budgetRepository,
        protected MobileAppSettingService $mobileAppSettingService,
    ) {}

    public function options(): array
    {
        return [
            'project_types' => $this->typeRepository->listActive()->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'sort_order' => (int) $item->sort_order,
            ])->values()->all(),
            'project_needs' => $this->needRepository->listActive()->map(fn ($item) => [
                'id' => $item->id,
                'mobile_event_project_type_id' => $item->mobile_event_project_type_id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'sort_order' => (int) $item->sort_order,
            ])->values()->all(),
            'packages' => $this->packageRepository->listActive()->map(fn ($item) => [
                'id' => $item->id,
                'mobile_event_project_need_id' => $item->mobile_event_project_need_id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'sort_order' => (int) $item->sort_order,
            ])->values()->all(),
            'budget_options' => $this->budgetRepository->listActive()->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'min_amount' => $item->min_amount,
                'max_amount' => $item->max_amount,
                'sort_order' => (int) $item->sort_order,
            ])->values()->all(),
            'consultation_fee' => $this->mobileAppSettingService->eventConsultationFee(),
        ];
    }
}
