<p>
    <span class="path">Sidebar → Sistem → Monitoring Sistem</span>
</p>

<div class="box warn">
    <span class="bt">Khusus Super Admin</span>
    Modul ini <b>hanya dapat diakses oleh Super Admin</b>. Admin biasa tidak melihat menunya. Isinya bersifat teknis —
    untuk memantau proses latar belakang aplikasi.
</div>

<p>Monitoring Sistem terdiri dari dua halaman:</p>

<h2 class="sec">1. Job & Antrean</h2>
<p>
    Menampilkan <b>pekerjaan latar belakang</b> (antrean) aplikasi — misalnya pengiriman email/notifikasi yang diproses
    di belakang layar. Ada dua daftar:
</p>
<table class="tbl">
    <tr><th style="width:150px;">Daftar</th><th>Arti</th></tr>
    <tr><td><b>Pending</b></td><td>Pekerjaan yang menunggu/dalam antrean untuk diproses.</td></tr>
    <tr><td><b>Failed (Gagal)</b></td><td>Pekerjaan yang gagal dijalankan, beserta pesan errornya.</td></tr>
</table>
<p>Tindakan yang tersedia:</p>
<ul>
    <li><b>Coba lagi</b> pekerjaan yang gagal (satu per satu atau semua sekaligus).</li>
    <li><b>Hapus</b> pekerjaan gagal (satu / semua), atau <b>hentikan</b> pekerjaan pending.</li>
</ul>

<h2 class="sec">2. Cron Schedule</h2>
<p>
    Menampilkan daftar <b>tugas terjadwal</b> yang berjalan otomatis (mis. tiap menit/jam/hari) — lengkap dengan jadwal
    dalam bahasa yang mudah ("Setiap hari (00:00)") dan waktu jalan berikutnya. Sebagian tugas dapat <b>dijalankan manual</b> saat dibutuhkan.
</p>

<div class="box warn">
    <span class="bt">Tindakan permanen</span>
    Menghapus/mengosongkan antrean bersifat <b>permanen</b> dan tidak bisa dibatalkan. Lakukan hanya bila Anda paham dampaknya.
    Bila ragu, konsultasikan dengan tim teknis.
</div>
<div class="box note">
    <span class="bt">Kapan modul ini berguna</span>
    Saat ada keluhan "email/notifikasi tidak terkirim", periksa daftar <b>Failed</b> di Job &amp; Antrean untuk melihat penyebabnya,
    lalu coba jalankan ulang.
</div>
