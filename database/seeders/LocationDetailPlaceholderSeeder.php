<?php

namespace Database\Seeders;

use App\Models\FormField;
use Illuminate\Database\Seeder;

/**
 * Isi placeholder multi-baris (contoh pengisian yang benar, ~2 baris) untuk
 * sub-input "Detail Alamat" dan "Alamat Utama" pada SEMUA field lokasi di form builder.
 *
 * Placeholder ≥2 baris ini tampil sebagai animasi mengetik bergantian di aplikasi
 * (tiap baris = 1 contoh). Nilai yang sudah diisi admin TIDAK ditimpa.
 *
 *   php artisan db:seed --class=LocationDetailPlaceholderSeeder
 */
class LocationDetailPlaceholderSeeder extends Seeder
{
    public function run(): void
    {
        // Detail Alamat — no. rumah / blok / RT-RW / patokan (2 baris).
        $phDetail = implode("\n", [
            'Contoh: No. 12, Blok C, RT 03/RW 05 — pagar hitam sebelah Masjid Al-Ikhlas.',
            'Tulis nomor rumah, blok/RT-RW, dan patokan terdekat agar tim mudah menemukan.',
        ]);

        // Alamat Utama — nama jalan / area / kelurahan (2 baris).
        $phAddress = implode("\n", [
            'Contoh: Jl. Melati No. 8, Kel. Sembalun Lawang, dekat pertigaan pasar.',
            'Tulis nama jalan, nomor, dan area/kelurahan lokasi pekerjaan.',
        ]);

        $count = 0;

        FormField::query()
            ->where('type', 'location')
            ->get()
            ->each(function (FormField $field) use ($phDetail, $phAddress, &$count) {
                $config = $field->config ?? [];

                // Hanya isi bila belum diisi admin — jangan timpa kustomisasi.
                if (empty($config['ph_detail'])) {
                    $config['ph_detail'] = $phDetail;
                }
                if (empty($config['ph_address'])) {
                    $config['ph_address'] = $phAddress;
                }

                $field->config = $config;
                $field->save();
                $count++;
            });

        $this->command?->info("LocationDetailPlaceholderSeeder: {$count} field lokasi diperbarui.");
    }
}
