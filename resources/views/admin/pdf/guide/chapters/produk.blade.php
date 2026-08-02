<p>
    <span class="path">Sidebar → Mobile App → Produk</span>
</p>
<p>
    Modul <b>Produk</b> mengelola katalog produk yang dijual di aplikasi. Admin dapat menambah, mengubah, menghapus,
    dan <b>mengimpor massal</b> produk dari Excel.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Tabel menampilkan: <b>Produk</b> (gambar + nama + SKU), <b>Kategori</b> (jalur taksonomi lengkap), <b>Harga</b>, <b>Stok</b>, <b>Pengaturan</b>, dan <b>Status</b> (Aktif/Nonaktif). Pencarian memakai kolom cari bawaan tabel.</p>

<h2 class="sec">Menambah / mengubah produk</h2>
<p>Klik <b>Tambah Produk</b> (atau ikon pensil untuk edit) — membuka halaman formulir. Field penting:</p>
<table class="tbl">
    <tr><th style="width:170px;">Field</th><th>Keterangan</th></tr>
    <tr><td><b>Nama</b> (wajib)</td><td>Nama produk, maks. 180 karakter.</td></tr>
    <tr><td><b>SKU</b></td><td>Kode unik produk. Boleh dikosongkan (dibuat otomatis). Dipakai sebagai kunci saat impor.</td></tr>
    <tr><td><b>Kategori</b></td><td>Kategori dari taksonomi (lihat bab Kategori).</td></tr>
    <tr><td><b>Harga</b> (wajib)</td><td>Harga jual (angka). <b>Harga Coret</b> opsional untuk menampilkan harga sebelum diskon.</td></tr>
    <tr><td><b>Stok</b>, <b>Berat</b></td><td>Jumlah stok dan berat (gram) untuk perhitungan ongkir.</td></tr>
    <tr><td><b>Cakupan Layanan</b></td><td><b>Semua layanan</b> atau <b>Layanan tertentu</b> (pilih layanan bila spesifik).</td></tr>
    <tr><td><b>Metode Kirim</b></td><td><b>Jasa kurir</b> atau <b>Kurir internal</b> (isi ongkir internal bila memilih internal).</td></tr>
    <tr><td><b>Bisa di-bundle</b>, <b>Unggulan</b>, <b>Aktif</b></td><td>Sakelar pengaturan tampilan &amp; ketersediaan produk.</td></tr>
    <tr><td><b>Gambar Utama</b></td><td>Foto produk (maks. ~4 MB).</td></tr>
</table>
<div class="box note">
    <span class="bt">Ongkir internal otomatis dikosongkan</span>
    Bila metode kirim diubah ke <b>Jasa kurir</b>, nilai ongkir internal otomatis dihapus meski sebelumnya diisi.
</div>

<h2 class="sec">Impor massal dari Excel</h2>
<p>Klik <b>Import Excel</b> — wizard 3 langkah:</p>
<ol class="steps">
    <li><b>Unggah</b> berkas <code>.xlsx/.xls/.csv</code> (maks. 10 MB). Sistem membaca judul kolomnya.</li>
    <li><b>Petakan kolom</b> file Anda ke kolom database. Kolom <b>Nama Produk</b> dan <b>Harga</b> wajib dipetakan.</li>
    <li><b>Jalankan</b>. Muncul ringkasan: berapa <b>baru</b>, <b>diperbarui</b>, dan <b>gagal</b>.</li>
</ol>

<div class="box warn">
    <span class="bt">SKU adalah kunci impor (upsert)</span>
    Saat impor, baris dengan SKU yang sudah ada akan <b>memperbarui</b> produk lama; SKU baru/kosong akan <b>membuat</b>
    produk baru. Jadi pastikan SKU konsisten agar produk tidak terduplikasi.
</div>
<div class="box tip">
    <span class="bt">Kategori otomatis dibuat</span>
    Pada impor, kolom kategori bisa ditulis berjenjang (mis. <code>Induk &gt; Sub</code>) — bila belum ada, kategorinya
    dibuat otomatis. Sesi unggah bisa kedaluwarsa; bila gagal, cukup unggah ulang.
</div>
