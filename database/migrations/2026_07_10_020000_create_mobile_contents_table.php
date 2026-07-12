<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // 'about' | 'terms'
            $table->string('title', 150);
            $table->longText('body')->nullable(); // HTML dari CKEditor
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('mobile_contents')->insert([
            [
                'key' => 'about',
                'title' => 'Tentang Aplikasi',
                'body' => '<p>Maninjau adalah aplikasi untuk memesan jasa tukang, renovasi, event organizer, hingga membeli material &amp; perabotan rumah — semua dalam satu tempat.</p><p>Kami menghubungkan kamu dengan tim teknis terpercaya, survei ke lokasi, dan proses pembayaran yang aman.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'terms',
                'title' => 'Syarat & Ketentuan',
                'body' => '<h4>1. Penggunaan Layanan</h4><p>Dengan menggunakan aplikasi Maninjau, kamu menyetujui untuk mematuhi seluruh ketentuan yang berlaku dan memberikan data yang benar.</p><h4>2. Pemesanan &amp; Survei</h4><p>Setiap pengajuan jasa dapat memerlukan survei ke lokasi. Biaya survei mengikuti pengaturan yang berlaku dan diinformasikan sebelum pembayaran.</p><h4>3. Pembayaran</h4><p>Pembayaran diproses melalui kanal resmi.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_contents');
    }
};
