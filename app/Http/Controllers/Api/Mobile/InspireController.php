<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileInspireCatalogService;
use Illuminate\Support\Facades\Log;

class InspireController extends ApiController
{
    public function __construct(
        protected MobileInspireCatalogService $mobileInspireCatalogService
    ) {}

    public function index()
    {
        try {
            return $this->success([
                'inspirations' => $this->mobileInspireCatalogService->all(),
                'featured' => $this->mobileInspireCatalogService->featured(),
                'categories' => $this->mobileInspireCatalogService->categories(),
            ], 'Daftar inspirasi berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile inspire error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat inspirasi.', 500);
        }
    }

    public function show(string $slug)
    {
        try {
            $inspire = $this->mobileInspireCatalogService->findBySlug($slug);

            if (! $inspire) {
                return $this->error('Inspirasi tidak ditemukan.', 404);
            }

            return $this->success([
                'inspiration' => $inspire,
            ], 'Detail inspirasi berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile inspire detail error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat detail inspirasi.', 500);
        }
    }
}
