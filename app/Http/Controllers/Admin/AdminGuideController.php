<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Panduan / log book penggunaan dashboard admin (PDF). Setiap fase menambah bab
 * pada registry di bawah + partial blade `admin.pdf.guide.chapters.{key}`.
 */
class AdminGuideController extends Controller
{
    public function preview()
    {
        return $this->pdf()->stream('Panduan-Admin-Maninjau.pdf');
    }

    public function download()
    {
        return $this->pdf()->download('Panduan-Admin-Maninjau.pdf');
    }

    private function pdf()
    {
        return Pdf::loadView('admin.pdf.guide.index', $this->data())
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);
    }

    private function data(): array
    {
        return [
            'appName' => config('app.name', 'Maninjau'),
            'generatedAt' => now()->translatedFormat('d F Y'),
            'groups' => $this->chapters(),
        ];
    }

    /**
     * Registry bab per bagian. Fase berikutnya tinggal menambah entri + partial.
     * @return array<int, array{title:string, chapters: array<int, array{key:string,title:string}>}>
     */
    private function chapters(): array
    {
        return [
            [
                'title' => 'Bagian 1 · Dasar Penggunaan',
                'chapters' => [
                    ['key' => 'pendahuluan', 'title' => 'Pendahuluan'],
                    ['key' => 'navigasi', 'title' => 'Login, Navigasi & Konsep Dashboard'],
                ],
            ],
            [
                'title' => 'Bagian 2 · Modul Mobile Inti',
                'chapters' => [
                    ['key' => 'overview', 'title' => 'Overview Mobile App'],
                    ['key' => 'users', 'title' => 'Users (Pengguna Aplikasi)'],
                    ['key' => 'otp-logs', 'title' => 'OTP Logs'],
                    ['key' => 'service-requests', 'title' => 'Service Requests (Pengajuan Layanan)'],
                    ['key' => 'proposal', 'title' => 'Proposal'],
                    ['key' => 'live-chat', 'title' => 'Live Chat'],
                    ['key' => 'notifications', 'title' => 'Notifications (Kirim Notifikasi)'],
                    ['key' => 'notification-templates', 'title' => 'Template Notifikasi'],
                ],
            ],
            [
                'title' => 'Bagian 3 · Katalog & Konten',
                'chapters' => [
                    ['key' => 'kategori', 'title' => 'Kategori (Taksonomi Layanan)'],
                    ['key' => 'services', 'title' => 'Services (Master Layanan)'],
                    ['key' => 'form-builder', 'title' => 'Form Builder'],
                    ['key' => 'produk', 'title' => 'Produk'],
                    ['key' => 'order-produk', 'title' => 'Order Produk'],
                    ['key' => 'penilaian-produk', 'title' => 'Penilaian Produk'],
                    ['key' => 'voucher', 'title' => 'Voucher'],
                    ['key' => 'promosi', 'title' => 'Promosi'],
                    ['key' => 'koleksi-data', 'title' => 'Koleksi Data'],
                ],
            ],
            [
                'title' => 'Bagian 4 · Tampilan & Pengaturan Mobile',
                'chapters' => [
                    ['key' => 'section-home', 'title' => 'Section Home'],
                    ['key' => 'home-layout', 'title' => 'Home Layout'],
                    ['key' => 'inspire', 'title' => 'Inspire (Inspirasi)'],
                    ['key' => 'app-content', 'title' => 'App Content'],
                    ['key' => 'support-contacts', 'title' => 'Support Contacts'],
                    ['key' => 'settings', 'title' => 'Settings (Pengaturan Aplikasi)'],
                ],
            ],
            [
                'title' => 'Bagian 5 · Tampilan Website (CMS)',
                'chapters' => [
                    ['key' => 'pages', 'title' => 'Pages (Halaman Website)'],
                    ['key' => 'blog', 'title' => 'Blog'],
                    ['key' => 'banner', 'title' => 'Banner'],
                    ['key' => 'galeri', 'title' => 'Galeri'],
                ],
            ],
            [
                'title' => 'Bagian 6 · Sistem & Akses',
                'chapters' => [
                    ['key' => 'modul', 'title' => 'Modul (Menu Dashboard)'],
                    ['key' => 'roles', 'title' => 'Roles & Hak Akses'],
                    ['key' => 'user-admin', 'title' => 'User (Akun Admin)'],
                    ['key' => 'pesan-email', 'title' => 'Pesan Email'],
                    ['key' => 'monitoring', 'title' => 'Monitoring Sistem'],
                    ['key' => 'profil', 'title' => 'Profil Akun'],
                ],
            ],
            [
                'title' => 'Penutup',
                'chapters' => [
                    ['key' => 'penutup', 'title' => 'Penutup'],
                ],
            ],
        ];
    }
}
