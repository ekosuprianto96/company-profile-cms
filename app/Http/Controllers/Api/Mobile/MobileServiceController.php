<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileServiceCatalogService;
use Illuminate\Support\Facades\Log;

class MobileServiceController extends ApiController
{
    public function __construct(
        protected MobileServiceCatalogService $mobileServiceCatalogService
    ) {}

    public function index()
    {
        try {
            return $this->success([
                'services' => $this->mobileServiceCatalogService->all(),
            ], 'Daftar layanan mobile berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile services error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat layanan mobile.', 500);
        }
    }
}

