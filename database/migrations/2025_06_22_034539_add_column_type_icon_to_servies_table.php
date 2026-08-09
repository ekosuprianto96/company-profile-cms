<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Idempoten: lewati kolom yang sudah ada (mis. DB hasil import) agar
            // migrasi tidak gagal "Duplicate column name".
            if (! Schema::hasColumn('services', 'type')) {
                $table->enum('type', ['icon', 'image'])->default('icon');
            }
            if (! Schema::hasColumn('services', 'url_image')) {
                $table->string('url_image')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('services', 'url_image')) {
                $table->dropColumn('url_image');
            }
        });
    }
};
