<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\HomeSection;
use App\Models\InspirePost;
use App\Models\MobileService;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class HomeSectionAdminService
{
    /** Semua section, urut tampilan. */
    public function list()
    {
        return HomeSection::withCount('items')->orderBy('sort_order')->orderBy('id')->get();
    }

    public function find(int $id): HomeSection
    {
        return HomeSection::with('items')->findOrFail($id);
    }

    /** id item terpilih (mode manual), urut. */
    public function selectedItemIds(?HomeSection $section): array
    {
        return $section
            ? $section->items->sortBy('sort_order')->pluck('item_id')->map(fn ($i) => (int) $i)->all()
            : [];
    }

    public function create(array $data, array $itemIds = []): HomeSection
    {
        return DB::transaction(function () use ($data, $itemIds) {
            $data['sort_order'] = $data['sort_order'] ?? ((int) HomeSection::max('sort_order') + 1);
            $section = HomeSection::create($data);
            $this->syncItems($section, $data['selection_mode'] ?? 'auto', $itemIds);

            return $section;
        });
    }

    public function update(int $id, array $data, array $itemIds = []): HomeSection
    {
        return DB::transaction(function () use ($id, $data, $itemIds) {
            $section = HomeSection::findOrFail($id);
            $section->update($data);
            $this->syncItems($section, $data['selection_mode'] ?? $section->selection_mode, $itemIds);

            return $section;
        });
    }

    public function delete(int $id): bool
    {
        return (bool) HomeSection::findOrFail($id)->delete(); // items ikut cascade
    }

    /** Geser urutan section satu langkah (up/down) dengan tukar sort_order tetangga. */
    public function reorder(int $id, string $direction): void
    {
        $section = HomeSection::findOrFail($id);
        $isUp = $direction === 'up';

        $neighbor = HomeSection::query()
            ->when($isUp,
                fn ($q) => $q->where('sort_order', '<', $section->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $section->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if (! $neighbor) {
            return;
        }

        DB::transaction(function () use ($section, $neighbor) {
            [$section->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $section->sort_order];
            $section->save();
            $neighbor->save();
        });
    }

    private function syncItems(HomeSection $section, string $mode, array $itemIds): void
    {
        $section->items()->delete();

        if ($mode !== 'manual') {
            return;
        }

        foreach (array_values(array_unique(array_map('intval', $itemIds))) as $order => $itemId) {
            if ($itemId > 0) {
                $section->items()->create(['item_id' => $itemId, 'sort_order' => $order]);
            }
        }
    }

    /**
     * Opsi item untuk picker manual, sesuai source. [{id,label}].
     */
    public function sourceItemOptions(string $source): array
    {
        return match ($source) {
            'product' => Product::orderBy('name')->limit(300)->get(['id', 'name'])
                ->map(fn ($p) => ['id' => $p->id, 'label' => $p->name])->all(),
            'service' => MobileService::orderBy('sort_order')->orderBy('title')->get(['id', 'title'])
                ->map(fn ($s) => ['id' => $s->id, 'label' => $s->title])->all(),
            'voucher' => Voucher::orderBy('code')->limit(300)->get(['id', 'code', 'name'])
                ->map(fn ($v) => ['id' => $v->id, 'label' => $v->code . ' — ' . $v->name])->all(),
            'inspire' => InspirePost::latest()->limit(300)->get(['id', 'title'])
                ->map(fn ($i) => ['id' => $i->id, 'label' => $i->title])->all(),
            'blog' => Blog::where('an', 1)->latest()->limit(300)->get(['id', 'title'])
                ->map(fn ($b) => ['id' => $b->id, 'label' => $b->title])->all(),
            default => [],
        };
    }
}
