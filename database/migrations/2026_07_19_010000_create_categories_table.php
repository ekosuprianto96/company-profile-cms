<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master data kategori — pohon kedalaman bebas (kategori → sub → sub-sub → …).
        // Dipakai bersama oleh layanan & produk. Ikon wajib.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete = pengaman DB: induk yang masih punya anak tidak bisa
            // dihapus (harus kosongkan anak dulu). Cek ramah juga dilakukan di service.
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 190)->unique();
            $table->string('icon', 80); // nama ikon (MaterialIcons), wajib
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
