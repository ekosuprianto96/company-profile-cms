<?php

namespace Database\Seeders;

use App\Models\MobileService;
use App\Models\MobileUser;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $serviceId = MobileService::query()->orderBy('id')->value('id');
        $targetUserIds = MobileUser::query()->orderBy('id')->limit(2)->pluck('id')->all();

        $vouchers = [
            [
                'code' => 'GRATISONGKIR',
                'name' => 'Gratis Ongkir',
                'description' => 'Potongan ongkir untuk pembelian produk. Tanpa minimum belanja.',
                'order_type' => 'product',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_purchase_amount' => 0,
                'usage_limit' => 500,
                'usage_limit_per_user' => 2,
                'expires_at' => now()->addMonths(2),
            ],
            [
                'code' => 'SURVEYHEMAT50',
                'name' => 'Diskon Survey Rp50.000',
                'description' => 'Potongan biaya survey dengan minimum belanja Rp50.000.',
                'order_type' => 'service',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_purchase_amount' => 50000,
                'usage_limit_per_user' => 1,
                'expires_at' => now()->addMonth(),
            ],
            [
                'code' => 'KILAT100',
                'name' => 'Diskon Kilat 100% (maks Rp10.000)',
                'description' => 'Diskon 100% dengan maksimal potongan Rp10.000.',
                'order_type' => 'service',
                'discount_type' => 'percentage',
                'discount_value' => 100,
                'max_discount_amount' => 10000,
                'min_purchase_amount' => 0,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'expires_at' => now()->addDays(14),
            ],
            [
                'code' => 'HEMATSURVEY10',
                'name' => 'Hemat 10% Biaya Survey',
                'description' => 'Diskon 10% biaya survey, maksimal Rp25.000.',
                'order_type' => 'service',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount_amount' => 25000,
                'min_purchase_amount' => 0,
                'expires_at' => now()->addMonths(2),
            ],
            [
                'code' => 'BELANJA15',
                'name' => 'Belanja Hemat 15%',
                'description' => 'Diskon 15% pembelian produk (maks Rp100.000), min. belanja Rp200.000.',
                'order_type' => 'product',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount_amount' => 100000,
                'min_purchase_amount' => 200000,
                'usage_limit_per_user' => 2,
                'expires_at' => now()->addMonths(2),
            ],
            [
                'code' => 'POTONGBELANJA25',
                'name' => 'Potongan Belanja Rp25.000',
                'description' => 'Potongan Rp25.000 untuk pembelian produk, min. belanja Rp150.000.',
                'order_type' => 'product',
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'min_purchase_amount' => 150000,
                'expires_at' => now()->addMonth(),
            ],
            [
                'code' => 'MEMBERSETIA30',
                'name' => 'Voucher Member Setia Rp30.000',
                'description' => 'Khusus member terpilih. Potongan Rp30.000 tanpa minimum belanja.',
                'order_type' => 'service',
                'discount_type' => 'fixed',
                'discount_value' => 30000,
                'min_purchase_amount' => 0,
                'user_scope' => $targetUserIds ? 'specific' : 'all',
                'usage_limit_per_user' => 1,
                'expires_at' => now()->addMonths(3),
                '__users' => $targetUserIds,
            ],
            [
                'code' => 'LAYANANSPESIAL20',
                'name' => 'Diskon 20% Layanan Pilihan',
                'description' => 'Diskon 20% (maks Rp50.000) untuk layanan tertentu.',
                'order_type' => 'service',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount_amount' => 50000,
                'min_purchase_amount' => 0,
                'item_scope' => $serviceId ? 'specific' : 'all',
                'expires_at' => now()->addMonths(2),
                '__services' => $serviceId ? [$serviceId] : [],
            ],
            [
                'code' => 'EXPIRED15',
                'name' => 'Voucher Kedaluwarsa Rp15.000',
                'description' => 'Contoh voucher yang sudah kedaluwarsa.',
                'order_type' => 'service',
                'discount_type' => 'fixed',
                'discount_value' => 15000,
                'min_purchase_amount' => 0,
                'expires_at' => now()->subDays(5),
            ],
            [
                'code' => 'NONAKTIF10',
                'name' => 'Voucher Nonaktif 10%',
                'description' => 'Contoh voucher yang sedang dinonaktifkan.',
                'order_type' => 'service',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount_amount' => 20000,
                'min_purchase_amount' => 0,
                'is_active' => false,
                'expires_at' => now()->addMonths(2),
            ],
        ];

        foreach ($vouchers as $data) {
            $userIds = $data['__users'] ?? [];
            $serviceIds = $data['__services'] ?? [];
            unset($data['__users'], $data['__services']);

            $data = array_merge([
                'max_discount_amount' => null,
                'min_purchase_amount' => 0,
                'item_scope' => 'all',
                'user_scope' => 'all',
                'usage_limit' => null,
                'usage_limit_per_user' => 1,
                'starts_at' => null,
                'expires_at' => null,
                'is_active' => true,
            ], $data);

            $voucher = Voucher::updateOrCreate(['code' => $data['code']], $data);

            $voucher->targetItems()->delete();
            foreach ($serviceIds as $sid) {
                $voucher->targetItems()->create(['target_type' => 'service', 'target_id' => (int) $sid]);
            }

            if ($userIds) {
                $voucher->targetUsers()->sync($userIds);
            } else {
                $voucher->targetUsers()->detach();
            }
        }

        $this->command?->info('Seeded ' . count($vouchers) . ' vouchers.');
    }
}
