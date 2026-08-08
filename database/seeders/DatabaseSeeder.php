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
            PageConfigSeeder::class,
            MobileAppSettingsSeeder::class,
            MobileServiceSeeder::class,
            InspirePostSeeder::class,
            HomeSectionPermissionSeeder::class,
            HomeSectionSeeder::class,
            VoucherTermsSeeder::class,
            FormPermissionSeeder::class,
            FormSeeder::class,
            EmailColorSchemeSeeder::class,
            EmailDesignSeeder::class,
            NotificationTemplateSeeder::class,
        ]);
    }
}
