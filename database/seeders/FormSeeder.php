<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\MobileService;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Seed form pengajuan layanan:
     *  1. "Pengajuan Umum" — replika form pengajuan yang berjalan sekarang.
     *  2. "Pengajuan IT Developer" — form lebih kompleks (termasuk upload PRD).
     * Sekaligus menautkan form ke layanan + mengisi skema harga tiap layanan.
     * Idempotent: kunci = slug form; field disusun ulang tiap dijalankan.
     */
    public function run(): void
    {
        $umum = $this->syncForm(
            'Pengajuan Umum',
            'pengajuan-umum',
            'Form standar pengajuan layanan: kebutuhan, budget, lokasi & jadwal survei.',
            $this->generalFields(),
        );

        $itDev = $this->syncForm(
            'Pengajuan IT Developer',
            'pengajuan-it-developer',
            'Form pengajuan proyek IT: kebutuhan produk, fitur, dokumen PRD, anggaran & PIC.',
            $this->itDeveloperFields(),
        );

        $event = $this->syncForm(
            'Pengajuan Event Organizer',
            'pengajuan-event-organizer',
            'Form pengajuan acara: jenis & jadwal acara, lokasi, konsep, kebutuhan layanan, dan anggaran.',
            $this->eventOrganizerFields(),
        );

        $this->attachToServices($umum, $itDev, $event);
        $this->seedPriceItems();
    }

    private function syncForm(string $name, string $slug, string $description, array $fields): Form
    {
        $form = Form::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $description, 'is_active' => true],
        );

        $form->fields()->delete();

        foreach ($fields as $order => $field) {
            $form->fields()->create(array_merge([
                'options_source' => 'static',
                'is_required' => false,
                'sort_order' => $order,
            ], $field));
        }

        return $form;
    }

    /** Form pengajuan yang berjalan sekarang, dipetakan ke schema builder. */
    private function generalFields(): array
    {
        return [
            ['key' => 'sec_kebutuhan', 'label' => 'Detail Kebutuhan', 'type' => 'section'],
            [
                'key' => 'need_type', 'label' => 'Jenis Kebutuhan', 'type' => 'select', 'is_required' => true,
                'options_source' => 'datasource', 'options_source_key' => 'need_types',
                'help_text' => 'Ruang lingkup pekerjaan yang kamu butuhkan.',
            ],
            [
                'key' => 'budget_option', 'label' => 'Perkiraan Budget', 'type' => 'select',
                'options_source' => 'datasource', 'options_source_key' => 'budget_options',
            ],
            [
                'key' => 'building_type', 'label' => 'Jenis Bangunan', 'type' => 'select',
                'options' => $this->opts(['Rumah Tinggal', 'Ruko', 'Apartemen', 'Kantor', 'Gudang', 'Lainnya']),
            ],
            [
                'key' => 'description', 'label' => 'Deskripsi Kebutuhan', 'type' => 'textarea', 'is_required' => true,
                'placeholder' => 'Ceritakan kebutuhanmu selengkap mungkin…',
                'validation' => ['max_length' => 5000],
            ],
            [
                'key' => 'issue_photos', 'label' => 'Foto Kondisi Saat Ini', 'type' => 'image',
                'help_text' => 'Opsional. Membantu tim memahami kondisi lapangan.',
                'validation' => ['accept' => 'jpg,jpeg,png,webp', 'max_size_mb' => 5, 'max_files' => 5],
            ],

            ['key' => 'sec_survei', 'label' => 'Lokasi & Jadwal Survei', 'type' => 'section'],
            [
                'key' => 'survey_location', 'label' => 'Lokasi Survei', 'type' => 'location', 'is_required' => true,
                'help_text' => 'Tentukan titik lokasi di peta, alamat & wilayah terisi otomatis.',
            ],
            [
                'key' => 'survey_date', 'label' => 'Jadwal Survei', 'type' => 'date', 'is_required' => true,
            ],
        ];
    }

    /** Form khusus IT Developer — pertanyaan dirancang mengikuti alur kerja proyek IT. */
    private function itDeveloperFields(): array
    {
        return [
            ['key' => 'sec_proyek', 'label' => 'Tentang Proyek', 'type' => 'section'],
            [
                'key' => 'project_type', 'label' => 'Jenis Proyek', 'type' => 'select', 'is_required' => true,
                'options' => $this->opts([
                    'Website Company Profile', 'Web Application / Dashboard', 'Aplikasi Mobile',
                    'Sistem Internal (ERP/POS)', 'E-Commerce', 'Integrasi / API', 'Lainnya',
                ]),
            ],
            [
                'key' => 'project_goal', 'label' => 'Tujuan & Masalah yang Ingin Diselesaikan', 'type' => 'textarea',
                'is_required' => true, 'placeholder' => 'Contoh: proses order masih manual via WA, ingin otomatis…',
                'validation' => ['min_length' => 20, 'max_length' => 3000],
            ],
            ['key' => 'target_user', 'label' => 'Siapa Target Penggunanya?', 'type' => 'text', 'placeholder' => 'Contoh: pelanggan retail, tim internal gudang'],
            [
                'key' => 'platforms', 'label' => 'Platform yang Dibutuhkan', 'type' => 'checkbox_group', 'is_required' => true,
                'options' => $this->opts(['Web', 'Android', 'iOS', 'Desktop']),
            ],

            ['key' => 'sec_fitur', 'label' => 'Kebutuhan Fitur', 'type' => 'section'],
            [
                'key' => 'main_features', 'label' => 'Fitur Utama', 'type' => 'checkbox_group',
                'options' => $this->opts([
                    'Login & Manajemen User', 'Pembayaran Online', 'Notifikasi Push/Email', 'Chat / Pesan',
                    'Dashboard & Laporan', 'Manajemen Konten (CMS)', 'Peta & Lokasi', 'Multi-bahasa', 'Multi-role / Hak Akses',
                ]),
            ],
            ['key' => 'other_features', 'label' => 'Fitur Lain di Luar Daftar', 'type' => 'textarea', 'placeholder' => 'Tulis kebutuhan khusus lainnya'],
            [
                'key' => 'integrations', 'label' => 'Integrasi Pihak Ketiga', 'type' => 'checkbox_group',
                'options' => $this->opts(['Payment Gateway', 'WhatsApp API', 'Google Maps', 'Email / SMTP', 'Sistem Akuntansi', 'API Internal Perusahaan']),
            ],

            ['key' => 'sec_dokumen', 'label' => 'Dokumen & Referensi', 'type' => 'section'],
            [
                'key' => 'has_prd', 'label' => 'Sudah Punya Dokumen PRD / Kebutuhan?', 'type' => 'radio', 'is_required' => true,
                'options' => $this->opts(['Sudah', 'Belum']),
                'help_text' => 'PRD = dokumen kebutuhan produk. Kalau belum ada, tim kami bantu susun.',
            ],
            [
                'key' => 'prd_document', 'label' => 'Upload Dokumen PRD', 'type' => 'file',
                'help_text' => 'Format PDF/DOC/DOCX, maks. 10MB per berkas.',
                'validation' => ['accept' => 'pdf,doc,docx', 'max_size_mb' => 10, 'max_files' => 3],
                'conditional' => ['field' => 'has_prd', 'operator' => 'eq', 'value' => 'Sudah'],
            ],
            [
                'key' => 'has_design', 'label' => 'Sudah Punya Desain / UI?', 'type' => 'radio',
                'options' => $this->opts(['Sudah lengkap', 'Sebagian', 'Belum ada']),
            ],
            [
                'key' => 'design_file', 'label' => 'Upload Desain / Mockup', 'type' => 'file',
                'validation' => ['accept' => 'pdf,png,jpg,jpeg,fig,zip', 'max_size_mb' => 20, 'max_files' => 5],
                'conditional' => ['field' => 'has_design', 'operator' => 'neq', 'value' => 'Belum ada'],
            ],
            ['key' => 'reference_links', 'label' => 'Referensi Aplikasi / Website', 'type' => 'textarea', 'placeholder' => 'Tempel tautan referensi, satu per baris'],

            ['key' => 'sec_anggaran', 'label' => 'Anggaran & Waktu', 'type' => 'section'],
            [
                'key' => 'budget_option', 'label' => 'Perkiraan Anggaran', 'type' => 'select',
                'options_source' => 'datasource', 'options_source_key' => 'budget_options',
            ],
            ['key' => 'target_deadline', 'label' => 'Target Selesai', 'type' => 'date', 'help_text' => 'Perkiraan kapan produk diharapkan siap dipakai.'],
            [
                'key' => 'need_maintenance', 'label' => 'Butuh Maintenance Setelah Rilis?', 'type' => 'radio',
                'options' => $this->opts(['Ya', 'Tidak', 'Belum tahu']),
            ],

            ['key' => 'sec_pic', 'label' => 'Kontak PIC', 'type' => 'section'],
            ['key' => 'pic_name', 'label' => 'Nama PIC', 'type' => 'text', 'is_required' => true],
            ['key' => 'pic_phone', 'label' => 'No. WhatsApp PIC', 'type' => 'phone', 'is_required' => true],
            ['key' => 'pic_email', 'label' => 'Email PIC', 'type' => 'email'],
            ['key' => 'note_closing', 'label' => 'Tim kami akan menghubungi PIC maksimal 1×24 jam kerja untuk sesi konsultasi.', 'type' => 'note'],
        ];
    }

    /** Form pengajuan acara — memanfaatkan master data event yang sudah ada. */
    private function eventOrganizerFields(): array
    {
        return [
            ['key' => 'sec_acara', 'label' => 'Tentang Acara', 'type' => 'section'],
            [
                'key' => 'event_type', 'label' => 'Jenis Acara', 'type' => 'select', 'is_required' => true,
                'options_source' => 'datasource', 'options_source_key' => 'event_project_types',
            ],
            [
                'key' => 'event_need', 'label' => 'Kebutuhan Acara', 'type' => 'select',
                'options_source' => 'datasource', 'options_source_key' => 'event_project_needs',
            ],
            [
                'key' => 'event_package', 'label' => 'Paket yang Diminati', 'type' => 'select',
                'options_source' => 'datasource', 'options_source_key' => 'event_packages',
                'help_text' => 'Opsional — tim kami bisa bantu menyesuaikan paket.',
            ],
            ['key' => 'event_name', 'label' => 'Nama Acara', 'type' => 'text', 'placeholder' => 'mis. Resepsi Pernikahan Eko & Rani'],
            ['key' => 'event_date', 'label' => 'Tanggal Acara', 'type' => 'date', 'is_required' => true],
            [
                'key' => 'guest_count', 'label' => 'Perkiraan Jumlah Tamu', 'type' => 'number',
                'placeholder' => '300', 'validation' => ['min' => 1, 'max' => 100000],
            ],
            [
                'key' => 'event_duration', 'label' => 'Durasi Acara', 'type' => 'select',
                'options' => $this->opts(['1 hari', '2 hari', 'Lebih dari 2 hari']),
            ],

            ['key' => 'sec_lokasi', 'label' => 'Lokasi & Konsep', 'type' => 'section'],
            [
                'key' => 'venue_status', 'label' => 'Lokasi Acara Sudah Ditentukan?', 'type' => 'radio', 'is_required' => true,
                'options' => $this->opts(['Sudah ada', 'Belum — butuh rekomendasi']),
            ],
            [
                'key' => 'event_location', 'label' => 'Lokasi Acara', 'type' => 'location',
                'help_text' => 'Tentukan titik lokasi di peta, alamat & wilayah terisi otomatis.',
                'conditional' => ['field' => 'venue_status', 'operator' => 'eq', 'value' => 'Sudah ada'],
            ],
            [
                'key' => 'concept', 'label' => 'Konsep / Tema Acara', 'type' => 'textarea',
                'placeholder' => 'mis. rustic outdoor, adat Minang, garden party…',
                'validation' => ['max_length' => 3000],
            ],
            [
                'key' => 'reference_photos', 'label' => 'Referensi Dekorasi', 'type' => 'image',
                'validation' => ['accept' => 'jpg,jpeg,png,webp', 'max_size_mb' => 5, 'max_files' => 5],
            ],

            ['key' => 'sec_kebutuhan_event', 'label' => 'Kebutuhan Layanan', 'type' => 'section'],
            [
                'key' => 'services_needed', 'label' => 'Layanan yang Dibutuhkan', 'type' => 'checkbox_group',
                'options' => $this->opts([
                    'Dekorasi', 'Katering', 'Dokumentasi (Foto/Video)', 'MC / Host', 'Hiburan / Band',
                    'Rias & Busana', 'Undangan', 'Souvenir', 'Sound System & Lighting', 'Transportasi',
                ]),
            ],
            [
                'key' => 'budget_option', 'label' => 'Perkiraan Anggaran', 'type' => 'select',
                'options_source' => 'datasource', 'options_source_key' => 'event_budget_options',
            ],

            ['key' => 'sec_pic_event', 'label' => 'Kontak PIC', 'type' => 'section'],
            ['key' => 'pic_name', 'label' => 'Nama PIC', 'type' => 'text', 'is_required' => true],
            ['key' => 'pic_phone', 'label' => 'No. WhatsApp PIC', 'type' => 'phone', 'is_required' => true],
            ['key' => 'note_event', 'label' => 'Tim event kami akan menghubungi PIC untuk sesi konsultasi konsep & anggaran.', 'type' => 'note'],
        ];
    }

    /** @param string[] $labels */
    private function opts(array $labels): array
    {
        return array_map(fn ($label) => ['label' => $label, 'value' => $label], $labels);
    }

    private function attachToServices(Form $umum, Form $itDev, Form $event): void
    {
        $itSlugs = ['web-developer', 'mobile-developer'];
        $eventSlugs = ['wedding-organizer', 'gathering', 'event'];

        MobileService::whereIn('slug', $itSlugs)->update(['form_id' => $itDev->id]);
        MobileService::whereIn('slug', $eventSlugs)->update(['form_id' => $event->id]);
        MobileService::whereNotIn('slug', array_merge($itSlugs, $eventSlugs))->update(['form_id' => $umum->id]);
    }

    /** Skema harga per layanan — menggantikan survey_fee global yang seragam. */
    private function seedPriceItems(): void
    {
        $plans = [
            // slug kategori induk => [type, label, amount]
            'pekerjaan-sipil-interior' => ['survey', 'Biaya Survei', 150000],
            'it-developer' => ['consultation', 'Biaya Konsultasi', 250000],
            'event-organizer' => ['consultation', 'Biaya Konsultasi', 200000],
            'travel-umroh' => ['registration', 'Biaya Pendaftaran', 500000],
        ];

        foreach (MobileService::with('category.parent.parent')->get() as $service) {
            $root = $service->category;
            while ($root && $root->parent) {
                $root = $root->parent;
            }

            $plan = $plans[$root->slug ?? ''] ?? null;
            if (! $plan) {
                continue;
            }

            $service->priceItems()->delete();
            $service->priceItems()->create([
                'type' => $plan[0],
                'label' => $plan[1],
                'amount' => $plan[2],
                'is_required' => true,
                'sort_order' => 0,
            ]);
        }
    }
}
