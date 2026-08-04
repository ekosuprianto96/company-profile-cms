<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_color_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->json('colors');            // array hex, mis. ["#275a56","#c8915c",...]
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_color_schemes');
    }
};
