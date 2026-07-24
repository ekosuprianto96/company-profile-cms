<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Section dinamis untuk home screen mobile — admin bisa atur urutan,
        // source, layout, dan cara pemilihan datanya.
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->nullable();
            $table->string('subtitle', 200)->nullable();
            $table->string('source_type', 30);            // product|service|package|voucher|inspire|blog
            $table->string('layout', 20)->default('slider'); // slider|list|grid
            $table->string('selection_mode', 10)->default('auto'); // auto|manual
            $table->string('auto_filter', 30)->nullable();   // newest|discount|popular|featured|top_rated|active...
            $table->unsignedSmallInteger('max_items')->default(8);
            $table->string('view_all_target', 60)->nullable(); // rute "lihat semua" opsional
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Item terpilih (dipakai saat selection_mode = manual). item_id merujuk ke
        // record sesuai source_type section-nya.
        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained('home_sections')->cascadeOnDelete();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['home_section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_sections');
    }
};
