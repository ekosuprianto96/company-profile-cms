<?php

namespace App\Services;

use App\Repositories\MobileBudgetOptionRepository;

class MobileBudgetOptionCatalogService
{
    public function __construct(
        protected MobileBudgetOptionRepository $mobileBudgetOptionRepository
    ) {}

    public function all(): array
    {
        return $this->mobileBudgetOptionRepository
            ->listActive()
            ->map(fn ($budgetOption) => [
                'id' => $budgetOption->id,
                'name' => $budgetOption->name,
                'slug' => $budgetOption->slug,
                'min_amount' => $budgetOption->min_amount,
                'max_amount' => $budgetOption->max_amount,
                'sort_order' => (int) $budgetOption->sort_order,
            ])
            ->values()
            ->toArray();
    }
}

