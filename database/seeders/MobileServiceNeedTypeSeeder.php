<?php

namespace Database\Seeders;

use App\Models\MobileServiceNeedType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MobileServiceNeedTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $needTypes = [
            [
                'name' => 'Perencanaan',
                'description' => 'Kebutuhan konsultasi, desain, RAB, dan perencanaan proyek.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Jasa & Bahan Bangunan',
                'description' => 'Paket lengkap jasa pengerjaan plus material.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Jasa',
                'description' => 'Pengerjaan jasa saja tanpa material dari tim.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($needTypes as $needType) {
            $name = (string) $needType['name'];

            MobileServiceNeedType::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => $needType['description'],
                    'sort_order' => $needType['sort_order'],
                    'is_active' => $needType['is_active'],
                ]
            );
        }
    }
}

