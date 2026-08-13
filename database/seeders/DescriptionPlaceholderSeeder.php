<?php

namespace Database\Seeders;

use App\Models\FormField;
use Illuminate\Database\Seeder;

/**
 * Isi placeholder multi-baris (contoh penulisan + contoh deskripsi jelas) untuk
 * SEMUA field "deskripsikan pekerjaan / deskripsi kebutuhan" di form builder.
 *
 * Placeholder multi-baris ini akan tampil sebagai animasi mengetik bergantian di
 * aplikasi (fitur animated placeholder untuk input teks, ≥2 baris).
 *
 *   php artisan db:seed --class=DescriptionPlaceholderSeeder
 */
class DescriptionPlaceholderSeeder extends Seeder
{
    public function run(): void
    {
        $placeholder = implode("\n", [
            'Contoh: Renovasi dapur 3×4 m — ganti keramik lantai & dinding, pasang kitchen set.',
            'Sebutkan jenis pekerjaan, ukuran/luas area, dan material yang diinginkan.',
            'Ceritakan kondisi saat ini dan hasil akhir yang Anda harapkan.',
            'Semakin detail (lokasi, kisaran budget, target waktu), makin akurat estimasi tim kami.',
        ]);

        // Target: field deskripsi (textarea) — berdasarkan peran data 'description',
        // key 'description', atau label yang mengandung "Deskripsi".
        $updated = FormField::query()
            ->where('type', 'textarea')
            ->where(function ($query) {
                $query->where('role', 'description')
                    ->orWhere('key', 'description')
                    ->orWhere('label', 'like', '%Deskripsi%');
            })
            ->update(['placeholder' => $placeholder]);

        $this->command?->info("DescriptionPlaceholderSeeder: {$updated} field deskripsi diperbarui.");
    }
}
