<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileServiceCatalogService;
use Illuminate\Support\Facades\Log;

class MobileServiceController extends ApiController
{
    public function __construct(
        protected MobileServiceCatalogService $mobileServiceCatalogService,
    ) {}

    /** Schema form pengajuan milik sebuah layanan (dirender mobile). */
    public function formSchema(string $slug)
    {
        try {
            $service = \App\Models\MobileService::with(['form.fields', 'priceItems'])
                ->where('slug', $slug)->where('is_active', true)->first();

            if (! $service) {
                return $this->error('Layanan tidak ditemukan.', 404);
            }

            return $this->success([
                'service' => ['id' => $service->id, 'title' => $service->title, 'slug' => $service->slug],
                // Layanan tanpa form dari builder memakai form standar generik,
                // sehingga seluruh layanan aktif konsisten lewat alur form dinamis.
                'form' => $service->form
                    ? app(\App\Services\FormSchemaService::class)->schema($service->form)
                    : app(\App\Services\FormSchemaService::class)->defaultSchema($service),
                'price_items' => $service->priceItems->map(fn ($item) => [
                    'type' => $item->type,
                    'label' => $item->label,
                    'amount' => (int) $item->amount,
                    'is_required' => (bool) $item->is_required,
                ])->values()->toArray(),
                'price_total' => (int) $service->priceItems->where('is_required', true)->sum('amount'),
                // Cakupan wilayah survei (untuk validasi lokasi di form).
                'survey_coverage' => app(\App\Services\MobileAppSettingService::class)->surveyCoverage(),
            ], 'Schema form berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Load service form schema error: ' . $th->getMessage());

            return $this->error('Gagal memuat schema form.', 500);
        }
    }

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

}
