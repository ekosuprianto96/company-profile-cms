<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Form;
use App\Models\MobileService;
use Illuminate\Database\Seeder;

/**
 * Form pengajuan khusus Travel Umrah + menautkannya ke layanannya.
 * Idempoten: aman dijalankan ulang (field disusun ulang).
 */
class TravelUmrahFormSeeder extends Seeder
{
    public function run(): void
    {
        $paketCollection = Collection::where('name', 'Kebutuhan Umrah')->first();
        $paketSource = $paketCollection ? 'collection:' . $paketCollection->id : null;

        $form = Form::updateOrCreate(
            ['slug' => 'pengajuan-travel-umrah'],
            ['name' => 'Pengajuan Travel Umrah', 'description' => 'Form pengajuan paket perjalanan umrah.', 'show_service_header' => true, 'is_active' => true],
        );

        // Susun ulang field.
        $form->fields()->delete();

        $fields = [
            ['type' => 'section', 'key' => 'sec_perjalanan', 'label' => 'Detail Perjalanan Umrah'],
            [
                'type' => 'select', 'key' => 'paket_umrah', 'label' => 'Paket Umrah', 'is_required' => true,
                'options_source' => $paketSource ? 'datasource' : 'manual',
                'options_source_key' => $paketSource,
                'options' => $paketSource ? null : [
                    ['label' => 'Full Ramadhan++ (30 Hari)', 'value' => 'ramadhan_30'],
                    ['label' => 'Paket 12 Hari', 'value' => 'paket_12'],
                    ['label' => 'Paket 9 Hari', 'value' => 'paket_9'],
                ],
                'help_text' => 'Pilih paket yang diminati. Detail final dikonfirmasi tim kami.',
            ],
            ['type' => 'number', 'key' => 'jumlah_jamaah', 'label' => 'Jumlah Jamaah', 'is_required' => true, 'placeholder' => 'mis. 2', 'validation' => ['min' => 1]],
            ['type' => 'date', 'key' => 'tanggal_keberangkatan', 'label' => 'Perkiraan Tanggal Keberangkatan', 'is_required' => true, 'role' => 'survey_date'],
            ['type' => 'text', 'key' => 'kota_keberangkatan', 'label' => 'Kota Keberangkatan', 'placeholder' => 'mis. Jakarta / Surabaya'],

            ['type' => 'section', 'key' => 'sec_pemesan', 'label' => 'Data Pemesan'],
            ['type' => 'text', 'key' => 'nama_pemesan', 'label' => 'Nama Pemesan', 'is_required' => true],
            ['type' => 'phone', 'key' => 'no_wa', 'label' => 'No. WhatsApp', 'is_required' => true, 'placeholder' => '08xxxx'],
            ['type' => 'email', 'key' => 'email_pemesan', 'label' => 'Email', 'placeholder' => 'nama@email.com'],

            ['type' => 'section', 'key' => 'sec_tambahan', 'label' => 'Tambahan'],
            [
                'type' => 'radio', 'key' => 'punya_paspor', 'label' => 'Sudah Punya Paspor?',
                'options_source' => 'manual',
                'options' => [['label' => 'Sudah', 'value' => 'sudah'], ['label' => 'Belum', 'value' => 'belum']],
            ],
            ['type' => 'textarea', 'key' => 'catatan', 'label' => 'Catatan Tambahan', 'role' => 'description', 'placeholder' => 'Permintaan khusus, jumlah kamar, dsb.'],
            ['type' => 'note', 'key' => 'note_umrah', 'label' => 'Tim kami akan menghubungi kamu maks. 1×24 jam kerja untuk konfirmasi paket & jadwal keberangkatan.'],
        ];

        foreach ($fields as $i => $f) {
            $form->fields()->create(array_merge([
                'is_required' => false,
                'options_source' => 'manual',
                'options_source_key' => null,
                'options' => null,
                'validation' => null,
                'conditional' => null,
                'role' => null,
                'placeholder' => null,
                'help_text' => null,
                'sort_order' => $i + 1,
            ], $f, ['sort_order' => $i + 1]));
        }

        // Tautkan ke layanan Travel Umrah/Umroh.
        MobileService::where('title', 'like', '%Umrah%')->orWhere('title', 'like', '%Umroh%')
            ->update(['form_id' => $form->id]);
    }
}
