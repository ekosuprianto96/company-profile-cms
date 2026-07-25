<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Koleksi data dinamis (master-data yang dibuat admin, bukan modul hardcode).
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('label_field')->nullable(); // field key yang dipakai sbg label saat jadi source
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Skema field tiap koleksi.
        Schema::create('collection_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('type')->default('text'); // text|textarea|number|boolean|select
            $table->json('options')->nullable();      // untuk type select
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['collection_id', 'key']);
        });

        // Baris data (entry) tiap koleksi — nilai disimpan JSON per field key.
        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->json('data');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['collection_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_entries');
        Schema::dropIfExists('collection_fields');
        Schema::dropIfExists('collections');
    }
};
