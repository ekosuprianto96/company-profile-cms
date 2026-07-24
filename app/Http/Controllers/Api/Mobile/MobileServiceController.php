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

    public function index(\Illuminate\Http\Request $request)
    {
        try {
            $services = $this->mobileServiceCatalogService->all();

            // Cakupan voucher (untuk tombol "Pakai"): hanya layanan yang berlaku.
            if ($request->filled('voucher_id')) {
                $voucher = \App\Models\Voucher::with('targetItems')->find((int) $request->voucher_id);
                if (! $voucher || $voucher->order_type !== 'service') {
                    $services = [];
                } else {
                    $ids = app(\App\Services\VoucherService::class)->scopedItemIds($voucher);
                    if ($ids !== null) {
                        $services = array_values(array_filter($services, fn ($s) => in_array($s['id'], $ids, true)));
                    }
                }
            }

            return $this->success([
                'services' => $services,
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
