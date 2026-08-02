<p>
    <span class="path">Mobile App → Overview → kartu "Koleksi Data"</span>
</p>
<p>
    <b>Koleksi Data</b> adalah <b>master-data dinamis</b> buatan admin — misalnya daftar "jenis kebutuhan", "perkiraan budget",
    atau daftar pilihan apa pun. Koleksi ini kemudian dipakai sebagai <b>sumber pilihan</b> di Form Builder.
</p>

<h2 class="sec">Konsep: 3 lapis</h2>
<table class="tbl">
    <tr><th style="width:130px;">Lapis</th><th>Arti</th></tr>
    <tr><td><b>Koleksi</b></td><td>Wadah data (mis. "Jenis Kebutuhan").</td></tr>
    <tr><td><b>Field</b></td><td>Struktur/kolom data di koleksi (mis. "Nama", "Keterangan").</td></tr>
    <tr><td><b>Entry</b></td><td>Baris data sesungguhnya (mis. "Renovasi", "Bangun Baru").</td></tr>
</table>

<h2 class="sec">Membuat & mengisi koleksi</h2>
<ol class="steps">
    <li>Klik <b>Buat Koleksi</b>, isi nama (dan deskripsi). Anda diarahkan ke halaman <b>Kelola</b>.</li>
    <li>Di blok <b>Field</b>, tambahkan struktur data. Tiap field punya <b>Label</b>, <b>Key</b> (huruf kecil), <b>Tipe</b> (Teks, Teks Panjang, Angka, Ya/Tidak, atau Pilihan), dan tanda <b>wajib</b>.</li>
    <li>Di blok <b>Data</b>, klik <b>Tambah Data</b> untuk mengisi entri (form menyesuaikan field yang Anda buat). Tombol ini aktif setelah minimal ada satu field.</li>
    <li>Di <b>Info Koleksi</b>, pilih <b>Field Label</b> (field yang jadi teks pilihan saat dipakai di form) dan pastikan <b>Aktif</b>.</li>
</ol>

<h2 class="sec">Memakai di Form Builder</h2>
<p>
    Koleksi yang <b>Aktif</b> otomatis muncul sebagai datasource di Form Builder dengan nama "Koleksi: {nama}".
    Saat sebuah field pilihan menunjuk koleksi ini, opsinya diambil dari entri koleksi tersebut.
</p>

<div class="box warn">
    <span class="bt">Hati-hati mengubah/menghapus</span>
    Menghapus koleksi ikut menghapus <b>semua field &amp; entri</b> di dalamnya. Bila koleksi masih dipakai form,
    pilihan pada form itu akan menjadi kosong. Mengganti <b>Key</b> field setelah ada data juga membuat data lama tak terbaca —
    ubah dengan hati-hati.
</div>
<div class="box note">
    <span class="bt">Koleksi nonaktif tak muncul di form</span>
    Hanya koleksi berstatus Aktif yang tersedia sebagai sumber data. Menonaktifkan koleksi menyembunyikannya dari Form Builder
    tanpa menghapus datanya.
</div>
