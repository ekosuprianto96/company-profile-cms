<p>
    <span class="path">Sidebar → User Management → User</span>
</p>
<p>
    Modul <b>User</b> mengelola <b>akun admin dashboard</b> (bukan pengguna aplikasi mobile). Di sini administrator membuat
    akun untuk staf, menetapkan <b>role</b>-nya, dan mengatur akses aplikasi Admin mobile.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Kolom: <b>Nama</b>, <b>Role</b>, <b>Tgl Lahir</b>, <b>Email</b>, <b>No. Telp</b>, <b>No. KTP</b>, <b>No. NIP</b>, dan <b>Akses Mobile</b>. Akun Anda sendiri tidak tampil di daftar ini (kelola lewat Profil).</p>

<h2 class="sec">Membuat akun admin</h2>
<ol class="steps">
    <li>Klik <b>Tambah Pengguna</b> — membuka halaman formulir.</li>
    <li>Isi identitas: <b>Nama</b>, <b>Email</b>, <b>Kata sandi</b>, <b>Tgl Lahir</b>, <b>No. Telp</b>, <b>No. KTP</b> (16 digit), <b>NIP</b>, dan <b>Alamat</b>.</li>
    <li>Pilih <b>Role</b> — ini menentukan hak akses akun (lihat bab Roles).</li>
    <li>Klik simpan.</li>
</ol>
<div class="box note">
    <span class="bt">Semua kolom identitas wajib</span>
    KTP, NIP, telepon, tanggal lahir, dan alamat harus terisi. <b>Foto</b> tidak diunggah di sini — tiap admin mengunggahnya
    sendiri lewat <b>Profil</b>.
</div>

<h2 class="sec">Mengubah & reset kata sandi</h2>
<p>
    Klik ikon pensil untuk <b>Edit</b>. Untuk mengganti kata sandi seorang admin, isi kolom <b>Password</b> di form edit
    (kosongkan bila tidak ingin mengubah). Tidak ada tombol reset terpisah.
</p>

<h2 class="sec">Akses aplikasi Admin mobile</h2>
<table class="tbl">
    <tr><th style="width:180px;">Tindakan</th><th>Arti</th></tr>
    <tr><td><b>Beri/Cabut Akses Mobile</b></td><td>Mengizinkan akun memakai <b>aplikasi Admin mobile</b>. Saat diberikan, muncul <b>Credential Key</b> untuk disalin (dipakai saat login di aplikasi admin).</td></tr>
    <tr><td><b>Regenerate Credential</b></td><td>Membuat ulang credential key. Key lama langsung tidak berlaku &amp; sesi aplikasi dicabut.</td></tr>
</table>

<div class="box warn">
    <span class="bt">Akses Mobile ≠ akses dashboard web</span>
    "Akses Mobile" hanya untuk <b>aplikasi Admin mobile</b>. Yang menentukan apa yang bisa dilakukan di <b>dashboard web</b>
    adalah <b>Role</b> akun (bab Roles). Mencabut akses mobile tidak menghapus akun atau akses webnya.
</div>
