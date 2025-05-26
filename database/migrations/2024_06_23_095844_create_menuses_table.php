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
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id_menu');
            $table->bigInteger('id_module')->unsigned();
            $table->foreign('id_module')->references('id_module')->on('modules');
            $table->string('nama');
            $table->string('deskripsi', 255);
            $table->string('url');
            $table->string('icon', 50);
            $table->integer('an')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
