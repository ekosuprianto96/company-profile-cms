<p>
    <span class="path">Mobile App → Overview → kartu "Voucher"</span>
</p>
<p>
    Modul <b>Voucher</b> mengelola kupon diskon untuk layanan atau produk. Admin membuat voucher; pengguna
    <b>meng-klaim</b>-nya lebih dulu di aplikasi sebelum bisa memakainya saat checkout.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Kolom: <b>Voucher</b> (kode + nama), <b>Order</b> (untuk layanan/produk), <b>Diskon</b>, <b>Kuota</b> (terpakai/limit), <b>Kedaluwarsa</b>, dan <b>Status</b> (Aktif/Nonaktif/Expired).</p>

<h2 class="sec">Membuat voucher</h2>
<p>Klik <b>Tambah Voucher</b> (form muncul di modal). Field penting:</p>
<table class="tbl">
    <tr><th style="width:170px;">Field</th><th>Keterangan</th></tr>
    <tr><td><b>Kode</b> (unik)</td><td>Kode yang dimasukkan pengguna (huruf besar otomatis).</td></tr>
    <tr><td><b>Berlaku untuk</b></td><td><b>Layanan</b> atau <b>Produk</b>.</td></tr>
    <tr><td><b>Jenis Diskon</b></td><td><b>Persentase</b> (maks. 100%, bisa dibatasi diskon maksimum) atau <b>Nominal</b> (rupiah tetap).</td></tr>
    <tr><td><b>Min. Pembelian</b></td><td>Batas belanja minimum agar voucher berlaku.</td></tr>
    <tr><td><b>Cakupan Item &amp; Pengguna</b></td><td><b>Semua</b> atau <b>Tertentu</b> (pilih layanan / pengguna target).</td></tr>
    <tr><td><b>Kuota</b></td><td>Batas total pemakaian (kosong = tak terbatas) dan batas per pengguna.</td></tr>
    <tr><td><b>Periode</b></td><td>Tanggal mulai &amp; kedaluwarsa (kedaluwarsa tidak boleh sebelum mulai).</td></tr>
    <tr><td><b>Syarat &amp; Ketentuan</b></td><td>Teks kaya (editor) yang tampil di detail voucher pada aplikasi.</td></tr>
</table>

<div class="box note">
    <span class="bt">Pengguna harus klaim dulu</span>
    Voucher tidak langsung bisa dipakai — pengguna meng-klaim-nya dulu (masuk ke "Voucher Saya"), baru bisa dipakai saat checkout
    selama masih dalam periode dan kuota tersedia.
</div>

<h2 class="sec">Memahami kuota</h2>
<p>
    Kolom <b>Kuota</b> menampilkan jumlah yang <b>sudah terpakai</b>. Saat pengguna mulai checkout, satu kuota
    "dipesan" sementara; bila pembayaran batal/gagal, pesanan kuota itu dilepas kembali otomatis (proses berkala tiap jam).
</p>
<div class="box warn">
    <span class="bt">Cakupan item produk belum tersedia</span>
    Untuk voucher <b>Produk</b>, pemilihan item spesifik belum aktif (menyusul). Penargetan item tertentu saat ini
    baru berlaku untuk voucher <b>Layanan</b>.
</div>
