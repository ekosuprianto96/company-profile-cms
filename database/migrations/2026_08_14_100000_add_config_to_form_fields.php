<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            // Konfigurasi tambahan per field (mis. field lokasi: tampil/sembunyi &
            // placeholder tiap sub-input). Generik agar bisa dipakai tipe lain.
            if (! Schema::hasColumn('form_fields', 'config')) {
                $table->json('config')->nullable()->after('conditional');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('form_fields', 'config')) {
                $table->dropColumn('config');
            }
        });
    }
};
