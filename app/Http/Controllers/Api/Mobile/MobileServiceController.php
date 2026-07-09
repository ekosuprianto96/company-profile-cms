<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileServiceCatalogService;
use App\Services\MobileEventProjectOptionsService;
use Illuminate\Support\Facades\Log;

class MobileServiceController extends ApiController
{
    public function __construct(
        protected MobileServiceCatalogService $mobileServiceCatalogService,
        protected MobileEventProjectOptionsService $mobileEventProjectOptionsService,
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

    public function eventOptions()
    {
        try {
            return $this->success([
                'event_options' => $this->mobileEventProjectOptionsService->options(),
            ], 'Pilihan event project berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile event options error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat pilihan event project.', 500);
        }
    }
}
