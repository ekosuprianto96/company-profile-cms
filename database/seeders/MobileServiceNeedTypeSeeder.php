<?php

namespace Database\Seeders;

use App\Models\MobileServiceNeedType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MobileServiceNeedTypeSeeder extends Seeder
{
    /**
     * Kebutuhan layanan (opsi ruang lingkup pengerjaan). Idempotent (kunci = slug).
     */
    public function run(): void
    {
        $needTypes = [
            [
                'slug' => 'perencanaan-bahan-pelaksana',
                'name' => 'Perencanaan, Bahan Bangunan, dan Pelaksana Proyek',
                'description' => 'Paket penuh: perencanaan/desain, material bangunan, dan tenaga pelaksana.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'bahan-pelaksana',
                'name' => 'Bahan Bangunan dan Pelaksana Proyek',
                'description' => 'Material bangunan beserta tenaga pelaksana, tanpa perencanaan.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'pelaksana',
                'name' => 'Pelaksana Proyek',
                'description' => 'Tenaga pelaksana saja, material dan perencanaan di luar paket.',
                'sort_order' => 3,
            ],
            // Paket untuk layanan Travel Umroh (varian durasi).
            [
                'slug' => 'umroh-full-ramadhan',
                'name' => 'Full Ramadhan++ (30 Hari)',
                'description' => 'Paket umroh penuh selama Ramadhan, 30 hari.',
                'sort_order' => 4,
            ],
            [
                'slug' => 'umroh-12-hari',
                'name' => 'Paket 12 Hari',
                'description' => 'Paket umroh reguler 12 hari.',
                'sort_order' => 5,
            ],
            [
                'slug' => 'umroh-9-hari',
                'name' => 'Paket 9 Hari',
                'description' => 'Paket umroh hemat 9 hari.',
                'sort_order' => 6,
            ],
        ];

        foreach ($needTypes as $needType) {
            MobileServiceNeedType::query()->updateOrCreate(
                ['slug' => $needType['slug']],
                [
                    'name' => $needType['name'],
                    'description' => $needType['description'],
                    'sort_order' => $needType['sort_order'],
                    'is_active' => true,
                ],
            );
        }

        // Bersihkan opsi lama yang sudah tidak dipakai (jika ada), lepas relasinya dulu.
        $legacy = MobileServiceNeedType::whereIn('slug', ['perencanaan', 'jasa-bahan-bangunan', 'jasa'])->get();
        foreach ($legacy as $old) {
            DB::table('mobile_service_need_type_relations')
                ->where('mobile_service_need_type_id', $old->id)
                ->delete();
            $old->delete();
        }
    }
}
