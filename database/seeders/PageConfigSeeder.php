<?php

namespace Database\Seeders;

use App\Models\PageConfig;
use Illuminate\Database\Seeder;

/**
 * Seed konfigurasi page builder & home section ke tabel page_configs.
 *
 * Sumber data: database/seeders/data/page.json & sections.json (hasil pulih dari
 * riwayat Git). Dipakai untuk mengembalikan data landing page di produksi tanpa
 * perlu file config/*.json.
 *
 * AMAN diulang: kalau key sudah punya data di DB, TIDAK ditimpa (mencegah
 * menimpa perubahan yang dibuat admin lewat dashboard).
 *
 *   php artisan db:seed --class=PageConfigSeeder
 */
class PageConfigSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            'page' => database_path('seeders/data/page.json'),
            'sections' => database_path('seeders/data/sections.json'),
        ];

        foreach ($sources as $key => $path) {
            if (! is_file($path)) {
                $this->command?->warn("page-config '{$key}': dilewati — file seed tidak ditemukan ({$path}).");
                continue;
            }

            $decoded = json_decode((string) file_get_contents($path), true);
            if (! is_array($decoded)) {
                $this->command?->warn("page-config '{$key}': dilewati — JSON tidak valid.");
                continue;
            }

            $existing = PageConfig::where('key', $key)->first();
            if ($existing && filled($existing->value)) {
                $this->command?->info("page-config '{$key}': dilewati — sudah ada data di DB (tidak ditimpa).");
                continue;
            }

            PageConfig::updateOrCreate(
                ['key' => $key],
                ['value' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );

            $this->command?->info("page-config '{$key}': berhasil di-seed (".strlen((string) json_encode($decoded)).' byte).');
        }
    }
}
