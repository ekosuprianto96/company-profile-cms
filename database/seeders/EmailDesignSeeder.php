<?php

namespace Database\Seeders;

use App\Models\EmailDesign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailDesignSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->designs() as $d) {
            EmailDesign::updateOrCreate(
                ['slug' => Str::slug($d['name'])],
                [
                    'name' => $d['name'],
                    'category' => $d['category'],
                    'description' => $d['description'],
                    'subject' => $d['subject'],
                    'html' => $this->buildHtml($d),
                    'is_active' => true,
                    'is_default' => true,
                ]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function designs(): array
    {
        return [
            [
                'name' => 'Selamat Datang', 'category' => 'Umum', 'accent' => '#275a56',
                'description' => 'Sambutan untuk pengguna baru.', 'subject' => 'Selamat datang di {{ app_name }}!',
                'heading' => 'Selamat Datang 👋', 'greeting' => true,
                'cta' => ['label' => 'Buka Aplikasi', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Kode Verifikasi (OTP)', 'category' => 'Keamanan', 'accent' => '#1f4d78',
                'description' => 'Pengiriman kode OTP / verifikasi.', 'subject' => 'Kode verifikasi {{ app_name }}',
                'heading' => 'Kode Verifikasi', 'greeting' => true, 'cta' => null,
                'footerNote' => 'Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengatasnamakan {{ app_name }}.',
            ],
            [
                'name' => 'Layanan Disetujui', 'category' => 'Transaksi', 'accent' => '#15803d',
                'description' => 'Pemberitahuan pengajuan disetujui.', 'subject' => 'Pengajuan Anda disetujui',
                'heading' => 'Pengajuan Disetujui ✅', 'greeting' => true,
                'cta' => ['label' => 'Lihat Detail', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Layanan Ditolak', 'category' => 'Transaksi', 'accent' => '#b45309',
                'description' => 'Pemberitahuan pengajuan ditolak / perlu tindakan.', 'subject' => 'Informasi pengajuan Anda',
                'heading' => 'Pengajuan Perlu Ditinjau', 'greeting' => true,
                'cta' => ['label' => 'Hubungi Kami', 'url' => 'mailto:{{ support_email }}'],
            ],
            [
                'name' => 'Pembayaran Berhasil', 'category' => 'Transaksi', 'accent' => '#0f766e',
                'description' => 'Konfirmasi pembayaran diterima.', 'subject' => 'Pembayaran berhasil diterima',
                'heading' => 'Pembayaran Berhasil 🎉', 'greeting' => true,
                'cta' => ['label' => 'Lihat Pesanan', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Pesanan Dikirim', 'category' => 'Transaksi', 'accent' => '#2563eb',
                'description' => 'Notifikasi pesanan dalam pengiriman.', 'subject' => 'Pesanan Anda sedang dikirim',
                'heading' => 'Pesanan Dikirim 🚚', 'greeting' => true,
                'cta' => ['label' => 'Lacak Pesanan', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Promo & Penawaran', 'category' => 'Marketing', 'accent' => '#ea580c',
                'description' => 'Kampanye promo / penawaran spesial.', 'subject' => 'Penawaran spesial untuk Anda 🎁',
                'heading' => 'Penawaran Spesial!', 'greeting' => false,
                'cta' => ['label' => 'Lihat Promo', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Pengumuman', 'category' => 'Informasi', 'accent' => '#475569',
                'description' => 'Pengumuman / informasi umum.', 'subject' => 'Pengumuman dari {{ app_name }}',
                'heading' => 'Pengumuman', 'greeting' => true, 'cta' => null,
            ],
            [
                'name' => 'Pengingat', 'category' => 'Informasi', 'accent' => '#ca8a04',
                'description' => 'Pengingat aktivitas / jadwal.', 'subject' => 'Pengingat dari {{ app_name }}',
                'heading' => 'Pengingat ⏰', 'greeting' => true,
                'cta' => ['label' => 'Buka Sekarang', 'url' => '{{ app_url }}'],
            ],
            [
                'name' => 'Notifikasi Umum (Minimal)', 'category' => 'Umum', 'accent' => '#334155',
                'description' => 'Desain polos serbaguna tanpa hiasan.', 'subject' => '{{ app_name }}',
                'heading' => null, 'greeting' => false, 'cta' => null,
            ],
        ];
    }

    /**
     * Bangun HTML dari komponen EmailBlocks (sama dengan blok di builder).
     * greeting sengaja TIDAK dipakai: desain ini jadi pembungkus {{ body }}, dan
     * body dari template notifikasi sudah memuat sapaan sendiri (hindari dobel "Halo …").
     */
    private function buildHtml(array $o): string
    {
        // Semua desain default memakai warna brand Maninjau (bukan warna-warni).
        return \App\Support\EmailBlocks::scaffold([
            'accent' => \App\Support\EmailBlocks::ACCENT,
            'heading' => $o['heading'] ?? null,
            'greeting' => false,
            'cta' => $o['cta'] ?? null,
            'note' => $o['footerNote'] ?? null,
        ]);
    }
}
