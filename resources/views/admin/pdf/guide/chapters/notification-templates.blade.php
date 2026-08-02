<p>
    <span class="path">Sidebar → Mobile App → Template Notifikasi</span>
</p>
<p>
    Modul <b>Template Notifikasi</b> mengatur <b>teks otomatis</b> yang dikirim sistem saat suatu <b>kejadian (event)</b> terjadi —
    misalnya OTP, order disetujui, atau pesan chat. Dengan template, admin bisa mengubah kalimatnya tanpa mengutak-atik program.
</p>

<div class="box note">
    <span class="bt">Beda dengan modul "Notifications"</span>
    <b>Notifications</b> = kirim pesan manual sekali jalan yang Anda tulis. <b>Template Notifikasi</b> = mengatur isi pesan
    <i>otomatis</i> yang dikirim sistem setiap kali event terjadi.
</div>

<h2 class="sec">Empat channel</h2>
<table class="tbl">
    <tr><th style="width:110px;">Channel</th><th>Keterangan</th></tr>
    <tr><td><b>Email</b></td><td>Dikirim ke email penerima. Mendukung format kaya (judul, tebal, daftar).</td></tr>
    <tr><td><b>Push</b></td><td>Notifikasi di layar HP (via FCM). Teks singkat; format otomatis jadi teks polos.</td></tr>
    <tr><td><b>In-app</b></td><td>Muncul di daftar notifikasi (lonceng) dalam aplikasi. Teks singkat.</td></tr>
    <tr><td><b>SMS</b></td><td>Pesan teks ke nomor HP (via <b>Zenziva</b>). Dipakai antara lain untuk OTP.</td></tr>
</table>
<p>Setiap template juga punya <b>audiens</b>: <b>Pengguna</b> (aplikasi) atau <b>Admin</b>.</p>

<h2 class="sec">Variabel dinamis</h2>
<p>
    Sisipkan variabel dengan tanda kurung kurawal ganda agar diganti data asli saat dikirim, contoh
    <code>@{{ app_name }}</code>, <code>@{{ recipient_name }}</code>, atau <code>@{{ otp_code }}</code>.
    Daftar variabel yang tersedia tampil sebagai tombol di halaman edit — klik untuk menyisipkannya.
</p>

<h2 class="sec">Mengedit template</h2>
<ol class="steps">
    <li>Di daftar, klik chip channel pada event yang ingin diubah (mis. <b>Email · Pengguna</b>).</li>
    <li>Ubah <b>Subjek/Judul</b> dan <b>Isi Pesan</b> pada editor. Sisipkan variabel bila perlu.</li>
    <li>Klik <b>Preview</b> untuk melihat hasil dengan data contoh sebelum menyimpan.</li>
    <li>Pastikan sakelar <b>Aktif</b> menyala agar template dipakai, lalu klik <b>Simpan</b>.</li>
</ol>

<h2 class="sec">Default, Custom, Reset, Duplikat</h2>
<table class="tbl">
    <tr><th style="width:120px;">Istilah</th><th>Arti</th></tr>
    <tr><td><b>Default</b></td><td>Template bawaan sistem.</td></tr>
    <tr><td><b>Custom</b></td><td>Template hasil ubahan/buatan admin.</td></tr>
    <tr><td><b>Reset ke Default</b></td><td>Mengembalikan teks ke bawaan semula.</td></tr>
    <tr><td><b>Duplikat</b></td><td>Menyalin template menjadi versi custom baru.</td></tr>
</table>

<div class="box tip">
    <span class="bt">Selalu Preview dulu</span>
    Sebelum menyimpan, klik <b>Preview</b> untuk memastikan variabel tergantikan benar dan kalimat rapi —
    terutama untuk SMS (hindari terlalu panjang) dan email.
</div>
<div class="box warn">
    <span class="bt">Email invoice dikecualikan</span>
    Email <b>invoice/tagihan</b> memakai format khusus tersendiri dan tidak diseragamkan dengan template notifikasi biasa.
</div>
