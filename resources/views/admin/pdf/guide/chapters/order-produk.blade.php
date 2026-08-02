<p>
    <span class="path">Sidebar → Mobile App → Order Produk</span>
</p>
<p>
    Modul <b>Order Produk</b> mengelola pesanan produk dari aplikasi — memantau status, memproses pengiriman,
    memverifikasi pembayaran, dan menerbitkan invoice.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Kolom: <b>Order</b> (nomor + tanggal), <b>Pelanggan</b>, <b>Produk</b> (nama + jumlah item), <b>Total</b>, dan <b>Status</b> (status pesanan + status bayar).</p>
<p>Tersedia: kotak <b>cari</b> (nomor order / pelanggan / produk), dropdown <b>Pembayaran</b>, <b>chip status</b>, filter <b>rentang tanggal</b>, serta <b>Export Excel/PDF</b> mengikuti filter.</p>

<h2 class="sec">Memproses pesanan (halaman detail)</h2>
<p>Detail menampilkan timeline status, daftar item, ringkasan biaya, data pelanggan &amp; pengiriman, dan panel <b>Proses Pesanan</b>. Di panel itu admin mengubah:</p>
<table class="tbl">
    <tr><th style="width:170px;">Kolom</th><th>Pilihan</th></tr>
    <tr><td><b>Status Pesanan</b></td><td>Masuk (pending) → Diproses → Dikemas → Dikirim → Selesai. Atau <b>Dibatalkan</b>.</td></tr>
    <tr><td><b>Status Pembayaran</b></td><td>Pending, <b>Lunas</b>, atau Gagal.</td></tr>
    <tr><td><b>No. Resi</b></td><td>Nomor resi pengiriman (diisi saat status Dikirim).</td></tr>
</table>
<p>Klik simpan pada panel untuk menerapkan. Semua perubahan lewat <b>satu dropdown status</b> (tidak ada tombol proses terpisah).</p>

<div class="box warn">
    <span class="bt">Efek otomatis saat status berubah</span>
    Menandai pesanan <b>Dibatalkan</b> mengembalikan <b>stok</b> produk. Menandai pembayaran <b>Lunas</b> menambah
    <b>jumlah terjual</b> produk dan mengunci pemakaian voucher. Efek ini berjalan sekali saat transisi pertama —
    jadi ubah status dengan hati-hati.
</div>

<h2 class="sec">Verifikasi transfer manual & invoice</h2>
<ul>
    <li>Bila metode bayar <b>transfer manual</b> dan belum lunas, panel menampilkan <b>bukti transfer</b> (gambar/PDF). Periksa, lalu set Status Pembayaran = <b>Lunas</b>.</li>
    <li>Tombol <b>Invoice</b> menghasilkan PDF tagihan (cetak atau unduh).</li>
    <li>Tombol <b>Chat Pelanggan</b> membuka percakapan di Live Chat dengan konteks order.</li>
</ul>

<div class="box note">
    <span class="bt">Catatan tentang label status</span>
    Pada sebagian data lama, status pesanan bisa tersimpan sebagai <code>menunggu_pembayaran</code>/<code>menunggu</code>,
    sedangkan pesanan baru memakai <code>pending</code>. Bila sebuah chip filter status terlihat tidak mencocokkan
    pesanan tertentu, coba chip <b>Semua</b> lalu gunakan pencarian.
</div>
