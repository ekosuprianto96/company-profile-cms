<p>
    <span class="path">Sidebar → Pengaturan → Roles</span>
</p>
<p>
    <b>Roles</b> (peran) menentukan <b>apa yang boleh dilakukan</b> tiap admin. Setiap akun admin memiliki <b>satu role</b>,
    dan role itulah yang membawa daftar izin (permission). Ini kunci keamanan dashboard.
</p>

<h2 class="sec">Bagaimana hak akses bekerja</h2>
<table class="tbl">
    <tr><th style="width:150px;">Unsur</th><th>Peran</th></tr>
    <tr><td><b>Role</b></td><td>Sekumpulan izin. Contoh: "Admin Konten", "Operator".</td></tr>
    <tr><td><b>Permission</b></td><td>Izin melakukan satu tindakan, mis. <code>product:create</code> (menambah produk).</td></tr>
    <tr><td><b>User</b></td><td>Akun admin — diberi tepat satu role (lihat bab User).</td></tr>
</table>
<p>Bila sebuah akun mencoba tindakan yang izinnya tidak dimiliki role-nya, sistem menolak (pesan "tidak diizinkan").</p>

<h2 class="sec">Mengelola role</h2>
<ol class="steps">
    <li>Klik <b>Tambah</b> untuk membuat role baru (isi Nama, Keterangan, Status).</li>
    <li>Pada baris role, klik <b>Setting Permission</b> (ikon roda gigi).</li>
    <li>Nyalakan/matikan sakelar izin sesuai kebutuhan role. Ada tombol <b>Pilih Semua</b> / <b>Batalkan Semua</b>.</li>
    <li>Klik simpan — daftar izin role diperbarui.</li>
</ol>

<div class="box warn">
    <span class="bt">Menyimpan izin menggantikan seluruh daftar</span>
    Saat menyimpan Setting Permission, izin yang tersimpan adalah <b>persis yang tercentang saat itu</b>. Bila Anda tidak
    sengaja mematikan semua lalu menyimpan, seluruh izin role tersebut tercabut. Periksa dengan teliti sebelum menyimpan.
</div>
<div class="box warn">
    <span class="bt">Hati-hati dengan role "superadmin"</span>
    Jangan mengubah atau menghapus role <b>superadmin</b> sembarangan — role ini biasanya menjadi pemegang akses tertinggi.
    Kesalahan dapat mengunci akses administrator.
</div>
<div class="box tip">
    <span class="bt">Prinsip hak seminimal mungkin</span>
    Beri role hanya izin yang benar-benar diperlukan untuk tugasnya. Semakin sempit izin, semakin aman dashboard dari kesalahan.
</div>
