<p>
    <span class="path">Sidebar → Mobile App → OTP Logs</span>
</p>
<p>
    <b>OTP Logs</b> adalah halaman <b>pemantauan</b> kode OTP (kode verifikasi sekali pakai) yang dikirim ke pengguna
    lewat <b>email</b> dan <b>SMS</b>. Berguna untuk menelusuri keluhan "kode tidak masuk" atau memastikan pengiriman berhasil.
</p>

<div class="box note">
    <span class="bt">Hanya untuk memantau</span>
    Modul ini <b>read-only</b> — tidak ada tombol setujui, hapus, atau kirim ulang. Fungsinya murni melihat riwayat.
</div>

<h2 class="sec">Kolom yang ditampilkan</h2>
<table class="tbl">
    <tr><th style="width:130px;">Kolom</th><th>Arti</th></tr>
    <tr><td><b>User</b></td><td>Nama + email/HP pemilik OTP.</td></tr>
    <tr><td><b>OTP Code</b></td><td>Kode OTP. <b>Hanya tampil untuk channel Email</b>; untuk SMS ditampilkan "-".</td></tr>
    <tr><td><b>Purpose</b></td><td>Keperluan OTP (mis. verifikasi, login).</td></tr>
    <tr><td><b>Channel</b></td><td><b>EMAIL</b> atau <b>SMS</b>.</td></tr>
    <tr><td><b>Recipient</b></td><td>Alamat email / nomor tujuan.</td></tr>
    <tr><td><b>Provider</b></td><td>Layanan pengirim (mis. Zenziva untuk SMS).</td></tr>
    <tr><td><b>Status</b></td><td>Keadaan OTP (lihat tabel di bawah).</td></tr>
    <tr><td><b>Attempts</b></td><td>Berapa kali kode dicoba dimasukkan.</td></tr>
    <tr><td><b>Timing</b></td><td>Waktu dikirim, kedaluwarsa, dan diverifikasi.</td></tr>
</table>

<h2 class="sec">Arti status</h2>
<table class="tbl">
    <tr><th style="width:130px;">Status</th><th>Arti</th></tr>
    <tr><td><b>Verified</b></td><td>Kode berhasil diverifikasi pengguna.</td></tr>
    <tr><td><b>Sent</b></td><td>Kode sudah terkirim, menunggu dimasukkan.</td></tr>
    <tr><td><b>Pending</b></td><td>Sedang diproses / menunggu pengiriman.</td></tr>
    <tr><td><b>Expired</b></td><td>Kode kedaluwarsa sebelum dipakai.</td></tr>
    <tr><td><b>Failed</b></td><td>Pengiriman gagal.</td></tr>
</table>

<div class="box tip">
    <span class="bt">Menelusuri keluhan OTP</span>
    Cari nama/nomor pengguna, lihat kolom <b>Status</b> dan <b>Timing</b>. Bila <b>Failed</b> pada SMS,
    cek modul <b>Template Notifikasi</b> &amp; pengaturan Zenziva. Bila <b>Expired</b>, minta pengguna meminta kode baru.
</div>
<div class="box note">
    <span class="bt">Mengapa kode SMS tidak terlihat?</span>
    Demi keamanan, isi kode SMS tidak ditampilkan di dashboard. Untuk email, kode bisa disalin lewat tombol salin — gunakan hanya untuk membantu pengguna, bukan disebar.
</div>
