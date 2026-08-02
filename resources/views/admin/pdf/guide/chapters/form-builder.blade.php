<p>
    <span class="path">Mobile App → Overview → kartu "Form Builder"</span>
</p>
<p>
    <b>Form Builder</b> memungkinkan admin membuat <b>formulir pengajuan dinamis</b> yang diisi pengguna saat memesan layanan —
    tanpa perlu memprogram. Form yang dibuat lalu dipasang ke layanan melalui modul <b>Services</b>.
</p>

<h2 class="sec">Membuat form</h2>
<ol class="steps">
    <li>Klik <b>Tambah Form</b>, isi <b>Nama</b>, deskripsi, dan sakelar <b>Aktif</b>.</li>
    <li>Buka <b>Builder</b> (ikon grid) pada form tersebut untuk menyusun field.</li>
    <li>Tambah field satu per satu; susun urutannya dengan <b>seret &amp; lepas</b>. Panel kanan menampilkan <b>pratinjau ponsel</b> secara langsung.</li>
</ol>
<div class="box warn">
    <span class="bt">Builder hanya di layar desktop</span>
    Penyusun field hanya berfungsi di layar lebar. Di layar kecil muncul pesan "Editor Form khusus Desktop".
</div>

<h2 class="sec">Jenis field</h2>
<p>Tersedia banyak tipe, dikelompokkan:</p>
<table class="tbl">
    <tr><th style="width:150px;">Kelompok</th><th>Tipe</th></tr>
    <tr><td><b>Isian teks</b></td><td>Teks singkat, teks panjang, angka, email, no. telepon</td></tr>
    <tr><td><b>Waktu</b></td><td>Tanggal, jam, tanggal &amp; jam</td></tr>
    <tr><td><b>Pilihan</b></td><td>Dropdown (satu / banyak), radio, checkbox (ya/tidak), checkbox banyak</td></tr>
    <tr><td><b>Unggahan</b></td><td>Upload gambar, upload dokumen</td></tr>
    <tr><td><b>Lokasi</b></td><td>Peta + alamat, dengan <b>wilayah terisi otomatis</b></td></tr>
    <tr><td><b>Tampilan</b></td><td>Judul bagian &amp; catatan (teks statis, bukan isian)</td></tr>
</table>

<h2 class="sec">Sumber pilihan (datasource)</h2>
<p>Untuk field pilihan, opsi bisa <b>diisi manual</b> atau <b>diambil dari master data</b>: Kategori, Layanan, Produk, atau <b>Koleksi Data</b> buatan admin. Inilah cara menyediakan pilihan seperti "jenis kebutuhan" atau "perkiraan budget" — buat datanya di modul <b>Koleksi Data</b>, lalu tunjuk di sini.</p>

<h2 class="sec">Fitur lanjutan</h2>
<ul>
    <li><b>Peran Data</b> — memetakan jawaban field ke data order (mis. lokasi survei, tanggal survei, foto kondisi), agar mengalir otomatis ke Order Layanan.</li>
    <li><b>Tampilkan Jika (kondisional)</b> — sebuah field hanya muncul bila jawaban field lain sama/tidak sama/terisi.</li>
    <li><b>Validasi</b> — panjang min/maks, angka min/maks, tipe &amp; ukuran berkas.</li>
    <li><b>Duplikat</b> — menyalin form (salinan dibuat nonaktif) untuk dijadikan dasar form baru.</li>
</ul>

<div class="box warn">
    <span class="bt">Form yang masih dipakai tidak bisa dihapus</span>
    Bila sebuah form masih terpasang di satu atau lebih layanan, penghapusan ditolak. Lepaskan dulu form dari
    layanannya (di modul Services) sebelum menghapus.
</div>
<div class="box note">
    <span class="bt">Hasil isian form → Proposal</span>
    Ketika pengguna mengisi dan mengirim form, jawabannya menjadi <b>Proposal</b> dan otomatis membuat <b>Order Layanan</b>
    (lihat bab Proposal &amp; Service Requests).
</div>
