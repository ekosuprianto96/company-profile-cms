<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherTermsSeeder extends Seeder
{
    /**
     * Isi Syarat & Ketentuan (rich text) untuk voucher yang belum punya.
     * Konten dibangun dari aturan voucher itu sendiri agar relevan.
     */
    public function run(): void
    {
        Voucher::all()->each(function (Voucher $voucher) {
            if (filled($voucher->terms)) {
                return;
            }

            $voucher->update(['terms' => $this->buildTerms($voucher)]);
        });
    }

    private function buildTerms(Voucher $voucher): string
    {
        $rp = fn ($n) => 'Rp' . number_format((int) $n, 0, ',', '.');

        $discount = $voucher->discount_type === 'percentage'
            ? "diskon {$voucher->discount_value}%" . ($voucher->max_discount_amount ? ' (maksimal potongan ' . $rp($voucher->max_discount_amount) . ')' : '')
            : 'potongan langsung ' . $rp($voucher->discount_value);

        $orderLabel = $voucher->order_type === 'product' ? 'pembelian produk' : 'pengajuan layanan';
        $minLine = $voucher->min_purchase_amount > 0
            ? '<li>Minimal transaksi ' . $rp($voucher->min_purchase_amount) . '.</li>'
            : '<li>Tanpa minimal transaksi.</li>';
        $expiry = $voucher->expires_at
            ? '<li>Voucher berlaku hingga <strong>' . $voucher->expires_at->translatedFormat('d F Y') . '</strong>.</li>'
            : '<li>Tidak memiliki tanggal kedaluwarsa.</li>';
        $perUser = '<li>Voucher dapat digunakan maksimal ' . (int) $voucher->usage_limit_per_user . ' kali per pengguna.</li>';

        return <<<HTML
<h3>Syarat &amp; Ketentuan Voucher {$voucher->code}</h3>
<p>Voucher ini memberikan <strong>{$discount}</strong> untuk {$orderLabel} di aplikasi Maninjau.</p>
<h4>Ketentuan Penggunaan</h4>
<ul>
    <li>Voucher harus diambil (klaim) terlebih dahulu sebelum dapat digunakan.</li>
    {$minLine}
    {$expiry}
    {$perUser}
    <li>Voucher hanya berlaku untuk {$orderLabel} yang termasuk dalam cakupan voucher.</li>
    <li>Voucher tidak dapat diuangkan, ditukar, atau digabung dengan promo lain kecuali dinyatakan lain.</li>
</ul>
<h4>Ketentuan Umum</h4>
<ul>
    <li>Maninjau berhak membatalkan transaksi bila terindikasi kecurangan atau penyalahgunaan voucher.</li>
    <li>Syarat &amp; ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan terlebih dahulu.</li>
</ul>
HTML;
    }
}
