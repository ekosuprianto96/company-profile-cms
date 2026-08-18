<?php

namespace Database\Seeders;

use App\Models\StepTemplate;
use App\Services\StepTemplateService;
use Illuminate\Database\Seeder;

/**
 * Seed Template Rules Step:
 * 1. "Alur Standar" — alur pengajuan yang berjalan sekarang (default semua layanan).
 * 2. "Alur Survey"  — alur revisi dari klien (gambar Status Pengajuan studio),
 *    pembayaran setelah disetujui + step survey manual.
 *
 * Idempotent: aman dijalankan berulang (updateOrCreate). Keterangan step yang
 * sudah diedit admin TIDAK tertimpa karena pencocokan per (template, key).
 *
 *   php artisan db:seed --class=StepTemplateSeeder
 */
class StepTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Template 1: Alur Standar (alur yang berjalan sekarang) =====
        $standard = StepTemplate::updateOrCreate(
            ['name' => 'Alur Standar'],
            [
                'description' => 'Alur pengajuan bawaan: draft → pembayaran → ditinjau admin → disetujui → selesai.',
                'is_default' => true,
                'is_active' => true,
            ],
        );

        $this->syncSteps($standard, array_map(
            fn (array $step, int $index) => $step + ['kind' => 'core', 'actions' => [], 'sort_order' => $index],
            StepTemplateService::coreSteps(),
            array_keys(StepTemplateService::coreSteps()),
        ));

        // ===== Template 2: Alur Survey (dari gambar klien) =====
        $survey = StepTemplate::updateOrCreate(
            ['name' => 'Alur Survey'],
            [
                'description' => 'Alur permintaan survey: diproses & disetujui dulu, lalu pembayaran, survey ke lokasi, dan penyerahan dokumen hasil.',
                'is_default' => false,
                'is_active' => true,
            ],
        );

        $this->syncSteps($survey, [
            [
                'key' => 'draft',
                'name' => 'Draft Permintaan Survey',
                'description' => 'Pengajuan permintaan Survey tersimpan.',
                'kind' => 'core',
                'trigger_status' => 'created',
                'actions' => [],
                'sort_order' => 0,
            ],
            [
                'key' => 'review',
                'name' => 'Permintaan Diproses',
                'description' => 'Tim kami sedang meninjau Permintaan Survey Anda.',
                'kind' => 'core',
                'trigger_status' => 'reviewed',
                'actions' => [],
                'sort_order' => 1,
            ],
            [
                'key' => 'approved',
                'name' => 'Permintaan Disetujui',
                'description' => 'Permintaan Survey telah disetujui.',
                'kind' => 'core',
                'trigger_status' => 'approved',
                'actions' => [],
                'sort_order' => 2,
            ],
            [
                'key' => 'waiting_payment',
                'name' => 'Lakukan Pembayaran',
                'description' => 'Untuk Survey silahkan lakukan pembayaran sesuai dengan opsi payment yang tersedia.',
                'kind' => 'core',
                'trigger_status' => 'payment_selected',
                'actions' => [],
                'sort_order' => 3,
            ],
            [
                'key' => 'paid',
                'name' => 'Pembayaran Berhasil',
                'description' => 'Pembayaran survey telah kami terima.',
                'kind' => 'optional',
                'trigger_status' => 'paid',
                'actions' => ['notif_inapp', 'notif_push'],
                'sort_order' => 4,
            ],
            [
                'key' => 'survey_waiting',
                'name' => 'Menunggu Survey',
                'description' => 'Tim kami akan menghubungi dan melakukan Survey ke lokasi.',
                'kind' => 'custom',
                'trigger_status' => null, // dicentang manual admin saat jadwal survey siap
                'actions' => ['notif_inapp', 'notif_push'],
                'sort_order' => 5,
            ],
            [
                'key' => 'completed',
                'name' => 'Survey Selesai Dilakukan',
                'description' => 'Tim kami akan memberikan output hasil Survey dalam bentuk dokumen. Silahkan cek aplikasi secara berkala.',
                'kind' => 'core',
                'trigger_status' => 'completed',
                'actions' => [],
                'sort_order' => 6,
            ],
        ]);

        $this->command?->info('StepTemplateSeeder: template "Alur Standar" (default) & "Alur Survey" siap.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     */
    protected function syncSteps(StepTemplate $template, array $steps): void
    {
        foreach ($steps as $step) {
            $existing = $template->steps()->where('key', $step['key'])->first();

            if ($existing) {
                // Jangan timpa nama/keterangan/action yang mungkin sudah diedit admin;
                // hanya pastikan atribut struktural tetap benar.
                $existing->update([
                    'kind' => $step['kind'],
                    'trigger_status' => $step['trigger_status'],
                    'sort_order' => $step['sort_order'],
                ]);

                continue;
            }

            $template->steps()->create($step);
        }
    }
}
