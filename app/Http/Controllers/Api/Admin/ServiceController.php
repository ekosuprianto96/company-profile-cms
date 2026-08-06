<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\MobileService;
use App\Services\MobileServiceAdminService;
use Illuminate\Http\Request;

class ServiceController extends ApiController
{
    public function __construct(protected MobileServiceAdminService $serviceAdmin) {}

    /** Daftar layanan untuk kontrol cepat di app admin. */
    public function index(Request $request)
    {
        $services = MobileService::orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return $this->success([
            'services' => $services->map(fn (MobileService $s) => $this->summary($s))->values(),
            'presets' => array_values(config('service_flags.pause_reason_presets', [])),
            'stats' => [
                'accepting' => $services->where('is_active', true)->where('submissions_paused', false)->count(),
                'paused' => $services->where('submissions_paused', true)->count(),
            ],
        ], 'Daftar layanan.');
    }

    /** Detail satu layanan (field pengaturan cepat). */
    public function show(int $id)
    {
        $service = MobileService::with('priceItems')->findOrFail($id);

        return $this->success([
            'service' => $this->detail($service),
            'presets' => array_values(config('service_flags.pause_reason_presets', [])),
        ], 'Detail layanan.');
    }

    /** Update pengaturan cepat (status, visibilitas, stop pengajuan, info, harga). */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:150'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_popular' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'is_coming_soon' => ['sometimes', 'boolean'],
            'submissions_paused' => ['sometimes', 'boolean'],
            'submissions_paused_note' => ['nullable', 'string', 'max:1000', 'required_if:submissions_paused,true,1'],
            'price_items' => ['sometimes', 'array'],
            'price_items.*.type' => ['nullable', 'string', 'max:30'],
            'price_items.*.label' => ['nullable', 'string', 'max:150'],
            'price_items.*.amount' => ['nullable', 'integer', 'min:0'],
            'price_items.*.is_required' => ['nullable', 'boolean'],
        ], [
            'submissions_paused_note.required_if' => 'Catatan alasan wajib diisi saat menghentikan penerimaan pengajuan.',
        ]);

        $service = $this->serviceAdmin->updateQuick($id, $data);

        return $this->success(['service' => $this->detail($service)], 'Layanan diperbarui.');
    }

    private function summary(MobileService $s): array
    {
        return [
            'id' => $s->id,
            'title' => $s->title,
            'is_active' => (bool) $s->is_active,
            'is_featured' => (bool) $s->is_featured,
            'is_popular' => (bool) $s->is_popular,
            'is_new' => (bool) $s->is_new,
            'is_coming_soon' => (bool) $s->is_coming_soon,
            'submissions_paused' => (bool) $s->submissions_paused,
            'submissions_paused_note' => $s->submissions_paused_note,
            'submissions_paused_at' => optional($s->submissions_paused_at)->toIso8601String(),
        ];
    }

    private function detail(MobileService $s): array
    {
        return $this->summary($s) + [
            'summary' => $s->summary,
            'description' => $s->description,
            'price_items' => $s->priceItems->map(fn ($item) => [
                'type' => $item->type,
                'label' => $item->label,
                'amount' => (int) $item->amount,
                'is_required' => (bool) $item->is_required,
            ])->values(),
        ];
    }
}
