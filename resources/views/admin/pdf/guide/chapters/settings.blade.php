<p>
    <span class="path">Mobile App → Overview → kartu "Settings"</span>
</p>
<p>
    Modul <b>Settings</b> berisi <b>pengaturan inti aplikasi</b> — nama aplikasi, layar pembuka, biaya &amp; pajak,
    pembayaran, invoice, dan cakupan wilayah survei. Disusun dalam beberapa tab. Setelah mengubah, klik simpan.
</p>

<h2 class="sec">Grup pengaturan</h2>
<table class="tbl">
    <tr><th style="width:150px;">Tab</th><th>Isi</th></tr>
    <tr><td><b>Aplikasi</b></td><td><b>Nama Aplikasi</b> — nama yang tampil di aplikasi &amp; kop invoice (default "Maninjau PRO" bila kosong).</td></tr>
    <tr><td><b>Onboarding</b></td><td>Slide layar pembuka: judul, subjudul, gambar, dan urutan.</td></tr>
    <tr><td><b>Biaya &amp; Pajak</b></td><td>Harga Survei, Biaya Konsultasi Event, <b>% Pajak</b>, dan <b>Masa Berlaku OTP</b> (menit).</td></tr>
    <tr><td><b>Pembayaran</b></td><td>Aktifkan <b>payment gateway</b> (provider: Midtrans) dan daftar <b>rekening transfer manual</b> (bank, nama, nomor).</td></tr>
    <tr><td><b>Invoice</b></td><td>Pilih template PDF invoice (layanan &amp; produk).</td></tr>
    <tr><td><b>Cakupan Survei</b></td><td>Area wilayah yang dilayani + nomor WhatsApp cadangan bila di luar jangkauan.</td></tr>
</table>

<h2 class="sec">Beberapa pengaturan penting</h2>
<ul>
    <li><b>Nama Aplikasi</b> — mengubahnya langsung mengganti nama di aplikasi tanpa perlu build ulang.</li>
    <li><b>Biaya &amp; Pajak</b> — Harga Survei, Biaya Konsultasi, dan % Pajak <b>wajib diisi</b>; total = biaya + (biaya × pajak%).</li>
    <li><b>Masa Berlaku OTP</b> — berapa menit kode OTP berlaku (1–60).</li>
    <li><b>Cakupan Survei</b> — tiap aturan bisa berlaku untuk <b>semua layanan</b> atau <b>layanan tertentu</b>. Daftar wilayah diambil dari layanan data wilayah eksternal.</li>
</ul>

<div class="box warn">
    <span class="bt">Kredensial Midtrans &amp; Zenziva tidak diatur di sini</span>
    Halaman ini hanya <b>mengaktifkan</b> payment gateway dan memilih provider. <b>Kunci API/kredensial</b> Midtrans (pembayaran)
    dan Zenziva (SMS OTP) diatur oleh tim teknis melalui berkas konfigurasi server (<code>.env</code>), bukan dari dashboard.
</div>
<div class="box note">
    <span class="bt">Perubahan langsung berlaku</span>
    Setelah menyimpan, sistem menyegarkan cache sehingga pengaturan baru segera dipakai aplikasi. Bila ada lapisan cache lain,
    tunggu beberapa saat hingga tampil.
</div>
