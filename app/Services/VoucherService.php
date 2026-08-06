<?php

namespace App\Services;

use App\Models\MobileUser;
use App\Models\Voucher;
use App\Models\VoucherClaim;
use Illuminate\Support\Carbon;

class VoucherService
{
    /** Hitung nominal diskon (rupiah) untuk sebuah subtotal. */
    public function calculateDiscount(Voucher $voucher, int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if ($voucher->discount_type === 'percentage') {
            $discount = (int) floor($subtotal * $voucher->discount_value / 100);
            if ($voucher->max_discount_amount) {
                $discount = min($discount, (int) $voucher->max_discount_amount);
            }
        } else {
            $discount = (int) $voucher->discount_value;
        }

        return max(0, min($discount, $subtotal));
    }

    /**
     * Alasan voucher tidak layak dipakai (null = layak). Asumsi voucher sudah
     * lolos filter aksesibilitas (order_type & user_scope) dari query pemanggil.
     */
    public function ineligibilityReason(Voucher $voucher, MobileUser $user, string $orderType, int $subtotal, ?int $itemId = null): ?string
    {
        if (! $voucher->is_active) {
            return 'Voucher tidak aktif.';
        }

        $now = Carbon::now();
        if ($voucher->starts_at && $now->lt($voucher->starts_at)) {
            return 'Belum berlaku.';
        }
        if ($voucher->expires_at && $now->gt($voucher->expires_at)) {
            return 'Sudah kedaluwarsa.';
        }
        if ($voucher->order_type !== $orderType) {
            return 'Tidak berlaku untuk order ini.';
        }
        if (! $voucher->isClaimedBy($user->id)) {
            return 'Ambil voucher ini dulu.';
        }
        if ($subtotal < (int) $voucher->min_purchase_amount) {
            return 'Min. belanja Rp' . number_format($voucher->min_purchase_amount, 0, ',', '.') . '.';
        }
        if ($voucher->item_scope === 'specific') {
            $targetIds = $voucher->targetItems->where('target_type', $orderType)->pluck('target_id')->all();
            if (! $itemId || ! in_array((int) $itemId, array_map('intval', $targetIds), true)) {
                return 'Tidak berlaku untuk item ini.';
            }
        }

        // Kuota per user (pakai agregat withCount bila tersedia; fallback query bila tidak).
        $userUsage = $voucher->user_redeem_count
            ?? $voucher->redemptions()->where('mobile_user_id', $user->id)->whereIn('status', ['reserved', 'used'])->count();
        if ($userUsage >= (int) $voucher->usage_limit_per_user) {
            return 'Batas pemakaian voucher tercapai.';
        }

        // Kuota global
        $activeCount = $voucher->active_redeem_count ?? $voucher->activeRedemptionCount();
        if ($voucher->usage_limit !== null && $activeCount >= (int) $voucher->usage_limit) {
            return 'Kuota voucher habis.';
        }

        return null;
    }

    /** Voucher yang bisa diakses user (public + tertarget) untuk sebuah order type, belum kedaluwarsa. */
    protected function accessibleQuery(MobileUser $user, string $orderType)
    {
        $now = Carbon::now();

        return Voucher::query()
            ->with(['targetItems', 'claims' => fn ($q) => $q->where('mobile_user_id', $user->id)])
            // Agregat kuota sekali jalan (hindari N+1 count per voucher).
            ->withCount([
                'redemptions as user_redeem_count' => fn ($q) => $q->where('mobile_user_id', $user->id)->whereIn('status', ['reserved', 'used']),
                'redemptions as active_redeem_count' => fn ($q) => $q->whereIn('status', ['reserved', 'used']),
            ])
            ->where('order_type', $orderType)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
            ->where(fn ($q) => $q->where('user_scope', 'all')
                ->orWhereHas('targetUsers', fn ($u) => $u->where('mobile_users.id', $user->id)));
    }

    /**
     * Daftar voucher tersedia untuk sheet pembayaran, di-group per tipe diskon.
     * Voucher tak layak tetap ditampilkan (dengan alasan) supaya bisa dibuat disabled.
     */
    public function availableForUser(MobileUser $user, string $orderType, int $subtotal, ?int $itemId = null): array
    {
        $vouchers = $this->accessibleQuery($user, $orderType)->orderByDesc('discount_value')->get();

        $groups = [
            'percentage' => ['type' => 'percentage', 'label' => 'Diskon Persen', 'vouchers' => []],
            'fixed' => ['type' => 'fixed', 'label' => 'Potongan Langsung', 'vouchers' => []],
        ];

        foreach ($vouchers as $voucher) {
            $reason = $this->ineligibilityReason($voucher, $user, $orderType, $subtotal, $itemId);
            $groups[$voucher->discount_type]['vouchers'][] = $this->payload($voucher, $subtotal, $reason, false, $voucher->isClaimedBy($user->id));
        }

        // urutkan tiap grup: layak dulu, lalu diskon terbesar
        return collect($groups)
            ->map(function ($group) {
                $group['vouchers'] = collect($group['vouchers'])
                    ->sortBy([['eligible', 'desc'], ['discount_amount', 'desc']])
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn ($group) => count($group['vouchers']) > 0)
            ->values()
            ->all();
    }

    /** Preview diskon untuk satu voucher (dipilih dari list atau via kode). */
    public function preview(MobileUser $user, string $orderType, int $subtotal, ?int $itemId, ?int $voucherId, ?string $code): array
    {
        $voucher = null;
        if ($voucherId) {
            $voucher = $this->accessibleQuery($user, $orderType)->whereKey($voucherId)->first();
        } elseif ($code) {
            $voucher = $this->accessibleQuery($user, $orderType)->where('code', strtoupper(trim($code)))->first();
        }

        if (! $voucher) {
            return ['valid' => false, 'message' => 'Kode voucher tidak ditemukan atau tidak berlaku.', 'voucher' => null];
        }

        $reason = $this->ineligibilityReason($voucher, $user, $orderType, $subtotal, $itemId);
        if ($reason) {
            return ['valid' => false, 'message' => $reason, 'voucher' => $this->payload($voucher, $subtotal, $reason)];
        }

        return ['valid' => true, 'message' => 'Voucher diterapkan.', 'voucher' => $this->payload($voucher, $subtotal, null)];
    }

    /** Daftar voucher untuk screen "Voucher Saya": active | used | expired. */
    public function listForUser(MobileUser $user, string $status): array
    {
        $now = Carbon::now();

        if ($status === 'used') {
            $vouchers = Voucher::query()
                ->whereHas('redemptions', fn ($r) => $r->where('mobile_user_id', $user->id)->where('status', 'used'))
                ->orderByDesc('id')->get();

            return $vouchers->map(fn ($v) => $this->payload($v, null, null, true, true))->all();
        }

        $accessible = Voucher::query()
            ->with(['targetItems', 'claims' => fn ($q) => $q->where('mobile_user_id', $user->id)])
            ->withCount(['redemptions as user_redeem_count' => fn ($q) => $q->where('mobile_user_id', $user->id)->whereIn('status', ['reserved', 'used'])])
            ->where(fn ($q) => $q->where('user_scope', 'all')
                ->orWhereHas('targetUsers', fn ($u) => $u->where('mobile_users.id', $user->id)))
            ->orderByDesc('id')
            ->get();

        $vouchers = $accessible->filter(function (Voucher $v) use ($user, $now, $status) {
            $usedUp = (int) $v->user_redeem_count >= (int) $v->usage_limit_per_user;
            $expired = (! $v->is_active) || ($v->expires_at && $now->gt($v->expires_at));

            if ($status === 'expired') {
                return $expired && ! $usedUp;   // kedaluwarsa & belum sempat dipakai
            }

            // active
            return ! $expired && ! $usedUp;
        });

        return $vouchers->map(fn ($v) => $this->payload($v, null, null, false, $v->isClaimedBy($user->id)))->values()->all();
    }

    /**
     * Item (produk/layanan) yang masuk cakupan voucher — untuk tombol "Pakai".
     * Return daftar id; null = semua item order_type-nya berlaku (item_scope all).
     */
    public function scopedItemIds(Voucher $voucher): ?array
    {
        if ($voucher->item_scope !== 'specific') {
            return null;
        }

        return $voucher->targetItems
            ->where('target_type', $voucher->order_type)
            ->pluck('target_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function payload(Voucher $voucher, ?int $subtotal = null, ?string $reason = null, bool $used = false, ?bool $claimed = null, bool $withTerms = false): array
    {
        $data = [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'description' => $voucher->description,
            'order_type' => $voucher->order_type,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (int) $voucher->discount_value,
            'max_discount_amount' => $voucher->max_discount_amount ? (int) $voucher->max_discount_amount : null,
            'min_purchase_amount' => (int) $voucher->min_purchase_amount,
            'expires_at' => optional($voucher->expires_at)?->toISOString(),
            'used' => $used,
        ];

        if ($claimed !== null) {
            $data['claimed'] = $claimed;
        }

        if ($withTerms) {
            $data['terms'] = $voucher->terms;
        }

        if ($subtotal !== null) {
            $discount = $this->calculateDiscount($voucher, $subtotal);
            $data['discount_amount'] = $discount;
            $data['final_amount'] = max(0, $subtotal - $discount);
            $data['eligible'] = $reason === null;
            $data['reason'] = $reason;
        }

        return $data;
    }

    /** Ambil (claim) voucher untuk user. Return payload voucher (dengan claimed=true). */
    public function claim(MobileUser $user, int $voucherId): array
    {
        $now = Carbon::now();

        $voucher = Voucher::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
            ->where(fn ($q) => $q->where('user_scope', 'all')
                ->orWhereHas('targetUsers', fn ($u) => $u->where('mobile_users.id', $user->id)))
            ->find($voucherId);

        if (! $voucher) {
            throw new \Exception('Voucher tidak tersedia untuk diambil.', 422);
        }

        VoucherClaim::firstOrCreate(
            ['voucher_id' => $voucher->id, 'mobile_user_id' => $user->id],
            ['claimed_at' => $now],
        );

        return $this->payload($voucher, null, null, false, true, true);
    }

    /** Detail satu voucher (dengan terms + status claimed) untuk halaman detail. */
    public function detail(MobileUser $user, int $voucherId): ?array
    {
        $voucher = Voucher::query()
            ->where('is_active', true)
            ->with(['claims' => fn ($q) => $q->where('mobile_user_id', $user->id)])
            ->find($voucherId);

        if (! $voucher) {
            return null;
        }

        return $this->payload($voucher, null, null, false, $voucher->isClaimedBy($user->id), true);
    }
}
