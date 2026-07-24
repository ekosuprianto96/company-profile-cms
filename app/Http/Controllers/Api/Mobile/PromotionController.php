<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\Promotion;
use Illuminate\Support\Facades\Log;

class PromotionController extends ApiController
{
    /**
     * Banner beranda yang sedang tayang, dipisah per penempatan:
     * `hero` = slider besar paling atas, `promo` = strip section promosi.
     */
    public function index()
    {
        try {
            $promotions = Promotion::live()->orderBy('sort_order')->orderByDesc('id')->get();
            $map = fn ($items) => $items->map(fn ($promotion) => $this->listPayload($promotion))->values()->all();

            return $this->success([
                'hero' => $map($promotions->where('placement', Promotion::PLACEMENT_HERO)),
                'promotions' => $map($promotions->where('placement', Promotion::PLACEMENT_PROMO)),
            ], 'Promosi berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Mobile promotions index error: ' . $th->getMessage());

            return $this->error('Gagal memuat promosi.', 500);
        }
    }

    public function show(string $slug)
    {
        try {
            $promotion = Promotion::where('slug', $slug)->first();

            if (! $promotion || ! $promotion->isRunning()) {
                return $this->error('Promosi tidak ditemukan atau sudah berakhir.', 404);
            }

            return $this->success(['promotion' => $this->detailPayload($promotion)], 'OK');
        } catch (\Throwable $th) {
            Log::error('Mobile promotion show error: ' . $th->getMessage());

            return $this->error('Gagal memuat promosi.', 500);
        }
    }

    /** @return array<string, mixed> */
    protected function listPayload(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id,
            'slug' => $promotion->slug,
            'placement' => $promotion->placement,
            'title' => $promotion->title,
            'summary' => $promotion->summary,
            'banner_image' => storageUrl($promotion->banner_image),
        ];
    }

    /** @return array<string, mixed> */
    protected function detailPayload(Promotion $promotion): array
    {
        return array_merge($this->listPayload($promotion), [
            'content' => $promotion->content,
            'cover_image' => storageUrl($promotion->cover_image ?: $promotion->banner_image),
            'cta_label' => $promotion->cta_label,
            'cta_url' => $promotion->cta_url,
            'starts_at' => optional($promotion->starts_at)?->toISOString(),
            'ends_at' => optional($promotion->ends_at)?->toISOString(),
        ]);
    }

}
