<p>
    <span class="path">Sidebar → Mobile App → Service Requests</span>
</p>
<p>
    Modul ini (judul halaman: <b>Order Layanan</b>) adalah tempat admin <b>meninjau dan memproses pengajuan layanan</b>
    dari aplikasi — mulai dari verifikasi pembayaran, menyetujui, hingga menandai selesai. Ini salah satu modul paling sering dipakai.
</p>

<h2 class="sec">Kartu ringkasan &amp; filter</h2>
<p>Di atas ada kartu ringkasan yang bisa <b>diklik untuk memfilter</b>: Menunggu Bayar, Verifikasi Transfer, Perlu Review, Aktif, dan Selesai.</p>
<p>Tabel bisa disaring lebih lanjut lewat: pencarian (kode order, nama/kontak pemesan, layanan, wilayah), dropdown layanan, chip status, serta filter lanjutan (status pembayaran, rentang tanggal survei, dan wilayah provinsi–kelurahan). Data bisa di-<b>Export Excel / PDF</b> mengikuti filter aktif.</p>

<h2 class="sec">Memahami status</h2>
<p>Setiap order punya dua status: <b>status order</b> dan <b>status pembayaran</b>.</p>
<table class="tbl">
    <tr><th style="width:170px;">Status Order</th><th>Arti</th></tr>
    <tr><td>Draft</td><td>Baru dibuat, belum diproses.</td></tr>
    <tr><td>Menunggu Pembayaran</td><td>Menunggu pengguna membayar.</td></tr>
    <tr><td>Menunggu Transfer / Verifikasi Bayar</td><td>Pengguna klaim sudah transfer; perlu diverifikasi admin.</td></tr>
    <tr><td>Disetujui</td><td>Sudah di-approve admin, layanan berjalan.</td></tr>
    <tr><td>Selesai</td><td>Layanan tuntas.</td></tr>
    <tr><td>Ditolak / Gagal</td><td>Order ditolak admin atau pembayaran gagal.</td></tr>
</table>
<table class="tbl">
    <tr><th style="width:170px;">Status Pembayaran</th><th>Arti</th></tr>
    <tr><td>Belum Bayar / Menunggu</td><td>Pembayaran belum masuk / diproses.</td></tr>
    <tr><td>Menunggu Transfer</td><td>Menunggu verifikasi bukti transfer manual.</td></tr>
    <tr><td>Lunas</td><td>Pembayaran terkonfirmasi.</td></tr>
    <tr><td>Gagal</td><td>Pembayaran gagal.</td></tr>
</table>

<h2 class="sec">Alur memproses order (di halaman detail)</h2>
<p>Tombol yang muncul <b>menyesuaikan status</b>. Urutan umumnya:</p>
<ol class="steps">
    <li><b>Verifikasi pembayaran</b> — untuk <b>transfer manual</b>, buka bukti transfer lalu klik <b>Lunas</b> (konfirmasi) atau <b>Tolak</b> (pengguna diminta unggah ulang). Pembayaran via gateway terverifikasi otomatis.</li>
    <li><b>Approve Order</b> — muncul setelah pembayaran <b>Lunas</b>. Isi catatan (opsional), lalu setujui.</li>
    <li><b>Tandai Selesai</b> — muncul saat status <b>Disetujui</b>, ketika pekerjaan tuntas.</li>
    <li><b>Reject Order</b> — bisa dilakukan selama order belum selesai/ditolak. <b>Alasan penolakan wajib diisi</b>.</li>
</ol>

<div class="box warn">
    <span class="bt">Harus lunas dulu sebelum disetujui</span>
    Tombol <b>Approve</b> baru muncul setelah pembayaran berstatus <b>Lunas</b>. Sebelum itu panel aksi menampilkan
    "Menunggu pembayaran user."
</div>

<h2 class="sec">Fitur pendukung di detail</h2>
<ul>
    <li><b>Chat</b> — membuka percakapan dengan pengguna di modul <b>Live Chat</b> (konteks order terbawa otomatis).</li>
    <li><b>PDF / Lihat Proposal</b> — mengunduh dokumen atau membuka <b>Proposal</b> asal order (bila order lahir dari form builder).</li>
    <li><b>Rincian biaya</b> — Biaya Survei/Konsultasi, Produk, Diskon, Pajak, dan Total.</li>
</ul>

<div class="box note">
    <span class="bt">Order tipe "event/project"</span>
    Sebagian layanan bertipe proyek/acara. Pada order ini istilah berubah otomatis: "Tgl Survei" → "Tgl Meeting",
    "Lokasi Survei" → "Lokasi Meeting", dan "Biaya Survei" → "Biaya Konsultasi". Alur persetujuannya sama.
</div>
