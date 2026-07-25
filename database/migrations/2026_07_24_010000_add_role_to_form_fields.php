<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            // Peran data: menandai field mana yang mengisi kolom Order Layanan
            // (lokasi survei, tanggal, foto, dst) tanpa bergantung pada nama kunci.
            $table->string('role', 40)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
