<?php

namespace Database\Seeders;

use App\Models\MobileEventBudgetOption;
use App\Models\MobileEventPackage;
use App\Models\MobileEventProjectNeed;
use App\Models\MobileEventProjectType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MobileEventProjectSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Wedding',
                'needs' => [
                    'Wedding dengan Konsep Syar’i',
                    'Wedding dengan Konsep Adat',
                ],
            ],
            [
                'name' => 'Exhibition',
                'needs' => [
                    'Exhibition Booth',
                    'Exhibition Full Event',
                ],
            ],
            [
                'name' => 'Gathering',
                'needs' => [
                    'Corporate Gathering',
                    'Community Gathering',
                ],
            ],
        ];

        $packages = [
            'Gedung / Tempat event, Konsumsi / Catering, Dekorasi, Nasihat Pernikahan, Souvenir & Dokumentasi',
            'Konsumsi / Catering, Dekorasi, Nasihat Pernikahan & Dokumentasi',
            'Konsumsi / Catering, Dekorasi, Nasihat Pernikahan',
        ];

        foreach ($types as $typeIndex => $typePayload) {
            $type = MobileEventProjectType::query()->updateOrCreate(
                ['slug' => Str::slug($typePayload['name'])],
                [
                    'name' => $typePayload['name'],
                    'description' => null,
                    'sort_order' => $typeIndex + 1,
                    'is_active' => true,
                ],
            );

            foreach ($typePayload['needs'] as $needIndex => $needName) {
                $need = MobileEventProjectNeed::query()->updateOrCreate(
                    [
                        'mobile_event_project_type_id' => $type->id,
                        'slug' => Str::slug($needName),
                    ],
                    [
                        'name' => $needName,
                        'description' => null,
                        'sort_order' => $needIndex + 1,
                        'is_active' => true,
                    ],
                );

                foreach ($packages as $packageIndex => $packageName) {
                    MobileEventPackage::query()->updateOrCreate(
                        [
                            'mobile_event_project_need_id' => $need->id,
                            'slug' => Str::slug($packageName),
                        ],
                        [
                            'name' => $packageName,
                            'description' => null,
                            'sort_order' => $packageIndex + 1,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }

        foreach ([
            ['name' => '100 Juta - 300 Juta', 'min_amount' => 100000000, 'max_amount' => 300000000],
            ['name' => '300 Juta - 500 Juta', 'min_amount' => 300000000, 'max_amount' => 500000000],
            ['name' => '500 Juta - 800 Juta', 'min_amount' => 500000000, 'max_amount' => 800000000],
            ['name' => '>= 800 Juta', 'min_amount' => 800000000, 'max_amount' => null],
        ] as $index => $budget) {
            MobileEventBudgetOption::query()->updateOrCreate(
                ['slug' => Str::slug($budget['name'])],
                [
                    'name' => $budget['name'],
                    'min_amount' => $budget['min_amount'],
                    'max_amount' => $budget['max_amount'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
