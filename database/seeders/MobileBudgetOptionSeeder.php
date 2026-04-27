<?php

namespace Database\Seeders;

use App\Models\MobileBudgetOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MobileBudgetOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'name' => '<= 50 Juta',
                'min_amount' => null,
                'max_amount' => 50000000,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => '>= 800 Juta',
                'min_amount' => 800000000,
                'max_amount' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => '100 Juta - 300 Juta',
                'min_amount' => 100000000,
                'max_amount' => 300000000,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => '300 Juta - 500 Juta',
                'min_amount' => 300000000,
                'max_amount' => 500000000,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => '500 Juta - 800 Juta',
                'min_amount' => 500000000,
                'max_amount' => 800000000,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($options as $option) {
            $name = (string) $option['name'];

            MobileBudgetOption::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'min_amount' => $option['min_amount'],
                    'max_amount' => $option['max_amount'],
                    'sort_order' => $option['sort_order'],
                    'is_active' => $option['is_active'],
                ]
            );
        }
    }
}

