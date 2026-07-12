<?php

namespace App\Services;

use App\Models\MobileService;
use App\Models\MobileUser;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherAdminService
{
    public function queryForAdmin()
    {
        return Voucher::query()->withCount([
            'redemptions as used_count' => fn ($q) => $q->where('status', 'used'),
        ])->orderByDesc('id');
    }

    public function find(int $id): Voucher
    {
        return Voucher::query()->with(['targetItems', 'targetUsers:id'])->findOrFail($id);
    }

    /** Layanan aktif untuk pilihan item-scope service. */
    public function services()
    {
        return MobileService::query()->orderBy('title')->get(['id', 'title']);
    }

    /** User mobile aktif untuk targeting. */
    public function users()
    {
        return MobileUser::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone', 'email']);
    }

    public function create(array $data, array $serviceIds = [], array $userIds = []): Voucher
    {
        return DB::transaction(function () use ($data, $serviceIds, $userIds) {
            $voucher = Voucher::create($data);
            $this->syncRelations($voucher, $serviceIds, $userIds);

            return $voucher;
        });
    }

    public function update(int $id, array $data, array $serviceIds = [], array $userIds = []): Voucher
    {
        return DB::transaction(function () use ($id, $data, $serviceIds, $userIds) {
            $voucher = $this->find($id);
            $voucher->update($data);
            $this->syncRelations($voucher, $serviceIds, $userIds);

            return $voucher->fresh(['targetItems', 'targetUsers']);
        });
    }

    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }

    protected function syncRelations(Voucher $voucher, array $serviceIds, array $userIds): void
    {
        // Item scope
        $voucher->targetItems()->delete();
        if ($voucher->item_scope === 'specific' && $voucher->order_type === 'service') {
            foreach (array_unique(array_filter($serviceIds)) as $serviceId) {
                $voucher->targetItems()->create([
                    'target_type' => 'service',
                    'target_id' => (int) $serviceId,
                ]);
            }
        }
        // (Item scope produk menyusul saat katalog produk tersedia.)

        // User scope
        if ($voucher->user_scope === 'specific') {
            $voucher->targetUsers()->sync(array_unique(array_filter($userIds)));
        } else {
            $voucher->targetUsers()->detach();
        }
    }
}
