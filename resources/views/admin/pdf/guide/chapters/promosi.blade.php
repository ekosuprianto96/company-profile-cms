<p>
    <span class="path">Mobile App → Overview → kartu "Promosi"</span>
</p>
<p>
    Modul <b>Promosi</b> mengatur <b>banner promosi</b> yang tampil di aplikasi — baik slider utama di beranda maupun
    bagian promo. Cocok untuk mengumumkan penawaran, event, atau informasi menarik.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Kolom: <b>Promosi</b> (thumbnail + judul + ringkasan), <b>Penempatan</b>, <b>Periode</b>, dan <b>Status</b>.</p>
<table class="tbl">
    <tr><th style="width:150px;">Status</th><th>Arti</th></tr>
    <tr><td><b>Tayang</b></td><td>Aktif dan sedang dalam periode — tampil di aplikasi.</td></tr>
    <tr><td><b>Di luar periode</b></td><td>Aktif, tetapi tanggalnya belum mulai atau sudah lewat (belum/tidak lagi tampil).</td></tr>
    <tr><td><b>Nonaktif</b></td><td>Dimatikan admin.</td></tr>
</table>

<h2 class="sec">Membuat promosi</h2>
<p>Klik <b>Tambah Promosi</b> (modal). Field penting:</p>
<table class="tbl">
    <tr><th style="width:160px;">Field</th><th>Keterangan</th></tr>
    <tr><td><b>Judul</b></td><td>Nama promosi (slug dibuat otomatis).</td></tr>
    <tr><td><b>Penempatan</b></td><td><b>Slider Utama</b> (hero di beranda) atau <b>Section Promosi</b>.</td></tr>
    <tr><td><b>Ringkasan &amp; Konten</b></td><td>Teks singkat + isi detail promosi.</td></tr>
    <tr><td><b>Tombol (CTA)</b></td><td>Label &amp; tautan tujuan saat banner diketuk.</td></tr>
    <tr><td><b>Periode</b></td><td>Tanggal mulai &amp; selesai (selesai tidak boleh sebelum mulai; boleh dikosongkan = tanpa batas).</td></tr>
    <tr><td><b>Urutan</b></td><td>Menentukan posisi bila ada beberapa promosi.</td></tr>
    <tr><td><b>Gambar</b></td><td><b>Banner</b> (tampil di daftar &amp; slider) dan <b>Cover</b> (untuk halaman detail). Keduanya opsional.</td></tr>
    <tr><td><b>Aktif</b></td><td>Sakelar tayang/tidak.</td></tr>
</table>

<div class="box tip">
    <span class="bt">Agar promosi tampil</span>
    Promosi hanya muncul di aplikasi bila <b>Aktif</b> <i>dan</i> tanggal sekarang berada dalam periode. Bila sudah dibuat
    tapi tak muncul, periksa status — kemungkinan "Di luar periode".
</div>
<div class="box note">
    <span class="bt">Izin menghapus</span>
    Menghapus promosi memerlukan izin khusus. Bila tombol hapus gagal, minta administrator menambahkan izin hapus promosi pada role Anda.
</div>
