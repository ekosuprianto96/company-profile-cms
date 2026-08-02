<p>
    <span class="path">Sidebar → Mobile App → Services</span>
</p>
<p>
    Modul <b>Services</b> adalah <b>master data layanan</b> yang tampil di aplikasi. Di sini admin mengatur judul,
    kategori, tampilan (ikon/warna), harga, dan <b>form pengajuan</b> yang dipakai tiap layanan.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Tabel punya dua tab: <b>Semua Layanan</b> dan <b>Tampil di Home</b>. Kolom mencakup Layanan (judul + tipe alur), Visual (warna kartu), Flags (Featured/Popular), Urutan, dan Status.</p>
<p>Filter tersedia untuk: <b>Kategori</b>, <b>Flow</b>, Featured, Popular, New, Coming Soon, dan Status.</p>
<div class="box note">
    <span class="bt">"Tampil di Home" dibatasi 7 layanan</span>
    Tab Home menampilkan maksimal <b>7 layanan</b> aktif (diurut unggulan lalu urutan). Angka ini menyesuaikan grid layanan
    di aplikasi — layanan ke-8 dan seterusnya tidak muncul di beranda.
</div>

<h2 class="sec">Menambah / mengubah layanan</h2>
<p>Tombol <b>Tambah Layanan</b> membuka halaman formulir penuh. Field utama:</p>
<table class="tbl">
    <tr><th style="width:160px;">Field</th><th>Keterangan</th></tr>
    <tr><td><b>Judul</b> (wajib)</td><td>Nama layanan. Slug dibuat otomatis.</td></tr>
    <tr><td><b>Kategori</b></td><td>Kategori dari taksonomi (lihat bab Kategori).</td></tr>
    <tr><td><b>Form Pengajuan</b></td><td>Memilih <b>Form</b> (dari Form Builder) yang akan diisi pengguna saat mengajukan. Satu form bisa dipakai banyak layanan.</td></tr>
    <tr><td><b>Tipe Alur</b></td><td><b>Standard</b> (survei) atau <b>Event/Project</b> (meeting/konsultasi) — memengaruhi istilah di order.</td></tr>
    <tr><td><b>Ikon</b></td><td><b>Ikon</b> (nama ikon) atau <b>Gambar</b> (unggah). Plus <b>gambar sampul</b>.</td></tr>
    <tr><td><b>Warna Kartu &amp; Teks</b></td><td>Warna hex untuk tampilan kartu layanan di aplikasi.</td></tr>
    <tr><td><b>Rincian Harga</b></td><td>Daftar item biaya (mis. Survei, Konsultasi, DP) — tiap item punya label, nominal, dan tanda <b>wajib/opsional</b>.</td></tr>
    <tr><td><b>Flags</b></td><td>Baru, Unggulan, Populer, Segera Hadir, dan Aktif.</td></tr>
</table>

<div class="box tip">
    <span class="bt">Menghubungkan layanan dengan form</span>
    Pengaitan form dilakukan dari <b>sini</b> (kolom Form Pengajuan), bukan dari Form Builder. Bila layanan tidak butuh
    isian khusus, form bisa dikosongkan (memakai form bawaan).
</div>
<div class="box warn">
    <span class="bt">Menghapus layanan permanen</span>
    Menghapus layanan juga menghapus file ikon &amp; sampulnya dari penyimpanan. Pastikan layanan benar-benar tak dipakai lagi.
</div>
