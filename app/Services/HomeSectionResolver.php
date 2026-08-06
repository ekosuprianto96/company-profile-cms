<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\HomeSection;
use App\Models\InspirePost;
use App\Models\MobileService;
use App\Models\MobileUser;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Carbon;

/**
 * Mengubah HomeSection (config) menjadi payload siap-render untuk mobile:
 * meta section + daftar item yang sudah di-resolve sesuai source, mode
 * pemilihan (auto/manual), filter, dan batas maksimum.
 */
class HomeSectionResolver
{
    public function __construct(
        protected MobileProductCatalogService $products,
        protected MobileServiceCatalogService $services,
        protected MobileInspireCatalogService $inspires,
        protected MobileBlogCatalogService $blogs,
        protected VoucherService $vouchers,
    ) {}

    /** User login (opsional) untuk memfilter voucher per-user. */
    protected ?MobileUser $currentUser = null;

    /** Semua section aktif (urut) yang punya minimal 1 item. */
    public function feed(?MobileUser $user = null): array
    {
        $this->currentUser = $user;

        return HomeSection::active()
            ->with('items')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomeSection $section) => $this->resolve($section))
            ->filter(fn (array $section) => ! empty($section['items']))
            ->values()
            ->all();
    }

    public function resolve(HomeSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'source_type' => $section->source_type,
            'layout' => $section->layout,
            'view_all_target' => $section->view_all_target,
            'items' => $this->items($section),
        ];
    }

    private function items(HomeSection $section): array
    {
        $limit = max(1, (int) $section->max_items);

        if ($section->isManual()) {
            $ids = $section->items->pluck('item_id')->take($limit)->all();

            return $this->mapManual($section->source_type, $ids);
        }

        return $this->auto($section->source_type, $section->auto_filter, $limit);
    }

    /** Mode otomatis: query per source + filter + limit. */
    private function auto(string $source, ?string $filter, int $limit): array
    {
        return match ($source) {
            'product' => $this->productQuery($filter)->limit($limit)->get()
                ->map(fn (Product $p) => $this->products->listItem($p))->all(),
            'service' => $this->serviceQuery($filter)->limit($limit)->get()
                ->map(fn (MobileService $s) => $this->services->listItem($s))->all(),
            'voucher' => $this->voucherQuery($filter)->limit($limit)->get()
                ->map(fn (Voucher $v) => $this->vouchers->payload($v))->all(),
            'inspire' => $this->inspireQuery($filter)->limit($limit)->get()
                ->map(fn (InspirePost $i) => $this->inspires->listItem($i))->all(),
            'blog' => $this->blogQuery($filter)->limit($limit)->get()
                ->map(fn (Blog $b) => $this->blogs->listItem($b))->all(),
            default => [], // package: belum ada
        };
    }

    /** Mode manual: ambil item terpilih dan pertahankan urutannya. */
    private function mapManual(string $source, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        [$models, $mapper] = match ($source) {
            'product' => [
                Product::with('masterCategory')->whereIn('id', $ids)->get()->keyBy('id'),
                fn (Product $p) => $this->products->listItem($p),
            ],
            'service' => [
                MobileService::with(['category.parent.parent', 'priceItems'])->whereIn('id', $ids)->get()->keyBy('id'),
                fn (MobileService $s) => $this->services->listItem($s),
            ],
            'voucher' => [
                Voucher::whereIn('id', $ids)->get()->keyBy('id'),
                fn (Voucher $v) => $this->vouchers->payload($v),
            ],
            'inspire' => [
                InspirePost::whereIn('id', $ids)->get()->keyBy('id'),
                fn (InspirePost $i) => $this->inspires->listItem($i),
            ],
            'blog' => [
                Blog::with('kategori')->whereIn('id', $ids)->get()->keyBy('id'),
                fn (Blog $b) => $this->blogs->listItem($b),
            ],
            default => [collect(), null],
        };

        if ($mapper === null) {
            return [];
        }

        return collect($ids)
            ->map(fn ($id) => $models->get($id))
            ->filter()
            ->map($mapper)
            ->values()
            ->all();
    }

    private function productQuery(?string $filter)
    {
        $q = Product::with('masterCategory')->where('is_active', true);

        return match ($filter) {
            'discount' => $q->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price')->latest(),
            'popular' => $q->orderByDesc('sold_count'),
            'featured' => $q->where('is_featured', true)->latest(),
            'top_rated' => $q->orderByDesc('rating'),
            default => $q->latest(),
        };
    }

    private function serviceQuery(?string $filter)
    {
        $q = MobileService::where('is_active', true)->with(['category.parent.parent', 'priceItems']);

        return match ($filter) {
            'popular' => $q->where('is_popular', true)->orderBy('sort_order'),
            'featured' => $q->where('is_featured', true)->orderBy('sort_order'),
            'coming_soon' => $q->where('is_coming_soon', true)->orderBy('sort_order'),
            'newest' => $q->latest(),
            default => $q->orderBy('sort_order'),
        };
    }

    private function voucherQuery(?string $filter)
    {
        $now = Carbon::now();
        $q = Voucher::where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            // Kuota total belum habis (usage_limit null = tak terbatas).
            ->where(fn ($w) => $w->whereNull('usage_limit')
                ->orWhereRaw('(select count(*) from voucher_redemptions vr where vr.voucher_id = vouchers.id and vr.status in ("reserved","used")) < vouchers.usage_limit'));

        // Bila user login: sembunyikan voucher yang batas pemakaian per-user-nya sudah tercapai.
        if ($this->currentUser) {
            $q->where(fn ($w) => $w->where('usage_limit_per_user', 0)
                ->orWhereRaw(
                    '(select count(*) from voucher_redemptions vr where vr.voucher_id = vouchers.id and vr.mobile_user_id = ? and vr.status in ("reserved","used")) < vouchers.usage_limit_per_user',
                    [$this->currentUser->id],
                ));
        }

        return $filter === 'newest' ? $q->latest() : $q->orderByRaw('expires_at IS NULL, expires_at ASC');
    }

    private function inspireQuery(?string $filter)
    {
        $q = InspirePost::where('is_published', true);

        if ($filter === 'featured') {
            $q->where('is_featured', true);
        }

        return $q->orderBy('sort_order')->latest();
    }

    private function blogQuery(?string $filter)
    {
        return Blog::with('kategori')->where('an', 1)->latest();
    }
}
