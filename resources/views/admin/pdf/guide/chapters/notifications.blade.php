<p>
    <span class="path">Sidebar → Mobile App → Notifications</span>
</p>
<p>
    Modul <b>Notifications</b> dipakai untuk <b>mengirim notifikasi manual (broadcast)</b> ke pengguna aplikasi —
    misalnya pengumuman, promo, atau informasi penting. Notifikasi dikirim sebagai <b>push</b> (muncul di layar HP)
    sekaligus <b>in-app</b> (masuk daftar notifikasi di dalam aplikasi).
</p>

<div class="box warn">
    <span class="bt">Beda dengan "Template Notifikasi"</span>
    Modul ini untuk <b>mengirim pesan sekali jalan yang Anda tulis sendiri</b>. Sedangkan
    <b>Template Notifikasi</b> mengatur <i>teks otomatis</i> yang dikirim sistem saat kejadian tertentu (OTP, order, chat).
    Keduanya berbeda — jangan tertukar.
</div>

<h2 class="sec">Mengirim notifikasi</h2>
<ol class="steps">
    <li>Buka <b>Notifications</b>, klik tombol <b>Kirim Notifikasi</b>.</li>
    <li>Pilih <b>Target Pengiriman</b>: <b>Semua User</b> atau <b>User Tertentu</b> (bila memilih ini, pilih nama pengguna di kolom yang muncul).</li>
    <li>Pilih <b>Tipe</b>: <b>Promo</b>, <b>Informasi</b>, atau <b>Konfirmasi</b> (memengaruhi tampilan/ikon di aplikasi).</li>
    <li>Isi <b>Judul</b> (maks. 120 karakter) dan <b>Pesan</b> (editor teks — bisa ditambah gambar).</li>
    <li>Opsional: isi <b>Link Tujuan</b> agar notifikasi bisa diketuk menuju halaman tertentu di aplikasi (disarankan memakai path relatif).</li>
    <li>Klik <b>Kirim</b>. Sistem mengirim push ke perangkat + menyimpan notifikasi in-app.</li>
</ol>

<h2 class="sec">Halaman daftar (inbox)</h2>
<p>
    Halaman utama menampilkan tabel notifikasi (judul, pesan, tipe, status baca, waktu) dengan filter dan pencarian.
</p>
<div class="box note">
    <span class="bt">Yang tampil adalah inbox admin, bukan kotak masuk tiap user</span>
    Tabel ini menampilkan notifikasi sistem milik <b>akun admin yang sedang login</b> (termasuk ringkasan "campaign berhasil dikirim"),
    bukan salinan persis yang diterima setiap pengguna. Gunakan ini untuk memastikan campaign terkirim, bukan sebagai riwayat per pengguna.
</div>

<div class="box tip">
    <span class="bt">Push hanya sampai ke perangkat aktif</span>
    Kirim "Semua User" hanya menyasar pengguna yang <b>aktif</b>, dan push hanya sampai ke perangkat yang punya
    token notifikasi aktif (pengguna yang mengizinkan notifikasi). In-app tetap tersimpan untuk dibuka saat aplikasi dibuka.
</div>
