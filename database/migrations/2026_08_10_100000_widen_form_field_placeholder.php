<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Placeholder kini bisa multi-baris (beberapa instruksi/contoh yang beranimasi),
        // jadi butuh ruang lebih dari varchar(255).
        Schema::table('form_fields', function (Blueprint $table) {
            $table->text('placeholder')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->string('placeholder', 255)->nullable()->change();
        });
    }
};
