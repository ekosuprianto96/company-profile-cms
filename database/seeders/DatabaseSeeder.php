<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MobileAppSettingsSeeder::class,
            MobileServiceNeedTypeSeeder::class,
            MobileBudgetOptionSeeder::class,
            MobileEventProjectSeeder::class,
            MobileServiceSeeder::class,
            InspirePostSeeder::class,
            HomeSectionPermissionSeeder::class,
            HomeSectionSeeder::class,
            VoucherTermsSeeder::class,
        ]);
    }
}
