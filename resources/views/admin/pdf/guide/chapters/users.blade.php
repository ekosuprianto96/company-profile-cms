<p>
    <span class="path">Sidebar → Mobile App → Users</span>
</p>
<p>
    Modul <b>Users</b> berisi seluruh <b>pengguna aplikasi mobile</b> (pelanggan). Di sini admin memantau,
    membuka detail, serta menonaktifkan atau memblokir akun bila diperlukan.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Tabel memuat kolom: <b>Nama</b>, <b>Kontak</b> (email &amp; nomor HP), <b>Verifikasi</b>, <b>Status</b>, <b>Aktivitas</b> (jumlah sesi &amp; login terakhir), dan <b>Terdaftar</b>.</p>
<table class="tbl">
    <tr><th style="width:150px;">Label</th><th>Arti</th></tr>
    <tr><td><b>Verified / Pending</b></td><td>Apakah email / nomor HP sudah diverifikasi pengguna.</td></tr>
    <tr><td><b>Aktif</b></td><td>Akun normal dan bisa memakai aplikasi.</td></tr>
    <tr><td><b>Nonaktif</b></td><td>Akun dinonaktifkan admin (lihat "Nonaktifkan" di bawah).</td></tr>
    <tr><td><b>Banned</b></td><td>Akun diblokir — tidak bisa memakai aplikasi dan melihat alasan blokir.</td></tr>
</table>
<div class="box note"><span class="bt">Pencarian</span> Kolom pencarian mencari berdasarkan <b>nama</b> dan <b>tanggal daftar</b>.</div>

<h2 class="sec">Empat tindakan pada tiap pengguna</h2>
<table class="tbl">
    <tr><th style="width:150px;">Tombol</th><th>Fungsi</th></tr>
    <tr><td><b>Detail</b> (ikon mata)</td><td>Membuka profil lengkap pengguna (lihat bawah).</td></tr>
    <tr><td><b>Nonaktifkan / Aktifkan</b></td><td>Mematikan/menyalakan akun tanpa alasan. Pengguna nonaktif tak bisa login, tapi tidak melihat pesan alasan.</td></tr>
    <tr><td><b>Cabut Sesi</b> (ikon kunci)</td><td>Mengeluarkan pengguna dari <b>semua perangkat</b> (memaksa login ulang). Akun tetap aktif — pengguna masih bisa login lagi.</td></tr>
    <tr><td><b>Blokir / Buka Blokir</b></td><td>Memblokir akun. Saat memblokir, admin dapat mengisi <b>alasan</b> (opsional) yang <b>ditampilkan ke pengguna</b>.</td></tr>
</table>

<div class="box warn">
    <span class="bt">Blokir tidak langsung terasa saat itu juga</span>
    Saat diblokir, sesi pengguna tidak dicabut serentak. Efeknya muncul pada <b>permintaan berikutnya</b> dari aplikasi:
    saat itu pengguna otomatis ditolak (melihat layar "Akun diblokir" beserta alasan) lalu keluar sendiri.
</div>
<div class="box tip">
    <span class="bt">Nonaktifkan vs Blokir</span>
    Gunakan <b>Nonaktifkan</b> untuk menahan akun sementara tanpa memberi tahu alasan.
    Gunakan <b>Blokir</b> untuk pelanggaran — pengguna akan melihat alasan yang Anda tulis.
</div>

<h2 class="sec">Halaman detail pengguna</h2>
<p>Menampilkan profil (avatar, kontak + status verifikasi, tanggal daftar, login terakhir) dan enam kartu statistik:</p>
<ul>
    <li><b>Order Layanan</b>, <b>Proposal</b>, <b>Order Produk</b>, <b>Voucher Diklaim</b>, <b>Alamat</b>, dan <b>Sesi Aktif</b>.</li>
</ul>
<p>Di bawahnya ada daftar <b>alamat</b> tersimpan serta tabel 10 data terakhir untuk order layanan, order produk, proposal, voucher, sesi/perangkat, dan OTP. Jika akun diblokir, muncul banner merah berisi waktu, admin pemblokir, dan alasan.</p>
