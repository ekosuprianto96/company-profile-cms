<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary')->nullable();       // ringkasan singkat di daftar
            $table->text('content')->nullable();          // detail & syarat-ketentuan
            $table->text('banner_image')->nullable();     // artwork strip di beranda
            $table->text('cover_image')->nullable();      // gambar besar di halaman detail
            $table->string('cta_label')->nullable();      // mis. "Ajukan Sekarang"
            $table->string('cta_url')->nullable();        // rute internal, mis. /service-request
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
