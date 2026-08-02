<p>
    <span class="path">Mobile App → Overview → kartu "Kategori"</span>
</p>
<p>
    Modul <b>Kategori</b> mengelola <b>taksonomi bertingkat</b> yang dipakai bersama oleh layanan dan produk —
    kategori dapat memiliki sub-kategori, dan sub-kategori bisa punya sub lagi tanpa batas kedalaman.
</p>

<div class="box note">
    <span class="bt">Cara membuka</span>
    Kategori tidak ada di sidebar. Buka lewat <b>Overview Mobile App</b> lalu klik kartu <b>Kategori</b>.
</div>

<h2 class="sec">Halaman daftar</h2>
<p>
    Kategori ditampilkan sebagai <b>pohon bertingkat</b> — anak kategori tampil menjorok ke dalam dengan penanda panah,
    dan induk yang punya anak diberi badge jumlah sub. Kolom: Nama, <b>Ikon</b>, dan <b>Status</b> (Aktif/Nonaktif).
</p>

<h2 class="sec">Menambah / mengubah kategori</h2>
<ol class="steps">
    <li>Klik <b>Tambah Kategori</b> (atau ikon pensil untuk mengubah).</li>
    <li>Isi <b>Nama</b>, pilih <b>Induk</b> (kosongkan bila kategori tingkat teratas), tulis nama <b>Ikon</b>, atur <b>Urutan</b>, dan sakelar <b>Aktif</b>.</li>
    <li>Klik <b>Simpan</b>.</li>
</ol>
<div class="box note">
    <span class="bt">Ikon berupa nama, bukan unggahan</span>
    Kolom Ikon diisi dengan <b>nama ikon</b> (string), bukan file gambar. Slug kategori dibuat otomatis dari nama.
</div>

<h2 class="sec">Menghapus</h2>
<div class="box warn">
    <span class="bt">Kategori yang punya sub tidak bisa dihapus</span>
    Untuk menghapus kategori induk, pindahkan/hapus dulu semua sub-kategorinya. Hanya kategori tanpa anak (daun) yang bisa dihapus.
    Sistem menolak (pesan "masih memiliki sub-kategori") bila dilanggar.
</div>

<div class="box tip">
    <span class="bt">Memilih induk otomatis mencakup turunannya</span>
    Saat memfilter layanan/produk berdasarkan sebuah kategori induk, semua sub-kategorinya ikut tercakup —
    jadi susun hierarki dari umum ke khusus agar penyaringan rapi.
</div>
