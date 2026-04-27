<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileBudgetOptionCatalogService;
use Illuminate\Support\Facades\Log;

class MobileBudgetOptionController extends ApiController
{
    public function __construct(
        protected MobileBudgetOptionCatalogService $mobileBudgetOptionCatalogService
    ) {}

    public function index()
    {
        try {
            return $this->success([
                'budget_options' => $this->mobileBudgetOptionCatalogService->all(),
            ], 'Daftar pilihan anggaran berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load mobile budget options error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat pilihan anggaran.', 500);
        }
    }
}

