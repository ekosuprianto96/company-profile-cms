<p>
    <span class="path">Order Layanan → detail order → Lihat Proposal</span>
    &nbsp;<span style="font-size:9px; color:#94a3b8;">(atau buka /admin/mobile/proposals)</span>
</p>
<p>
    <b>Proposal</b> adalah <b>dokumen isian</b> yang dibuat pengguna saat mengajukan layanan lewat <b>form dinamis (form builder)</b>.
    Isinya merekam seluruh jawaban form pengguna beserta rincian kebutuhan dan biaya.
</p>

<div class="box note">
    <span class="bt">Satu proposal = satu Order Layanan</span>
    Saat pengguna mengirim form, sistem otomatis membuat <b>Proposal</b> sekaligus <b>Order Layanan</b> yang saling tertaut.
    Proposal menyimpan <i>isi pengajuan</i>, sedangkan <b>status &amp; pembayaran dikelola di Order Layanan</b> — bukan di sini.
</div>

<h2 class="sec">Cara membuka</h2>
<p>
    Menu Proposal tidak selalu ada di sidebar. Cara termudah: buka <b>Order Layanan</b> → detail sebuah order →
    tombol <b>Lihat Proposal</b>. Halaman daftar proposal juga dapat diakses lewat alamat <code>/admin/mobile/proposals</code>.
</p>

<h2 class="sec">Halaman daftar</h2>
<p>Menampilkan 25 proposal terbaru dengan kolom: <b>Nomor</b> (format <code>PRP-tanggal-urut</code>), <b>Pemohon</b>, <b>Layanan</b>, <b>Status</b>, <b>Total</b>, dan <b>Dikirim</b>.</p>
<div class="box warn">
    <span class="bt">Kolom "Status" mengikuti Order Layanan</span>
    Status yang tampil di daftar adalah status <b>Order Layanan</b> terkait (mis. Menunggu Pembayaran, Disetujui, Selesai),
    bukan status proposal itu sendiri. Bila belum ada order, tertulis "Belum ada order".
</div>

<h2 class="sec">Halaman detail</h2>
<p>Berisi:</p>
<ul>
    <li><b>Rincian Kebutuhan</b> — seluruh jawaban form pengguna. Label pertanyaan tetap sesuai kondisi saat pengguna mengisi (memakai snapshot form), jadi akurat meski form kemudian diubah. Lampiran/gambar tampil sebagai tautan.</li>
    <li><b>Pemohon</b> — nama, HP, email.</li>
    <li><b>Rincian Biaya</b> — daftar item (Wajib/Opsional) beserta nominal dan Total.</li>
    <li><b>Order Layanan</b> — panel menuju order terkait untuk memproses status &amp; pembayaran.</li>
</ul>

<h2 class="sec">Mengunduh PDF</h2>
<p>Tombol <b>Preview PDF</b> membuka dokumen di tab baru; <b>Unduh PDF</b> menyimpannya (nama file = nomor proposal). Cocok untuk arsip atau dikirim ke pelanggan.</p>

<div class="box tip">
    <span class="bt">Mengelola proposal = mengelola ordernya</span>
    Untuk menyetujui/menolak, lakukan dari <b>Order Layanan</b>. Proposal murni dokumen — halaman ini untuk membaca isi
    pengajuan dan mengunduh PDF-nya.
</div>
