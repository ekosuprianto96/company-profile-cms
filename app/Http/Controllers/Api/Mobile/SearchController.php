<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileSearchCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends ApiController
{
    public function __construct(
        protected MobileSearchCatalogService $mobileSearchCatalogService
    ) {}

    public function popular(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'location_label' => 'nullable|string|max:255',
            ]);

            return $this->success([
                'popular' => $this->mobileSearchCatalogService->popular(
                    $validated['location_label'] ?? null,
                    isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                    isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                ),
            ], 'Daftar pencarian populer berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile popular search error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat pencarian populer.', 500);
        }
    }
}
