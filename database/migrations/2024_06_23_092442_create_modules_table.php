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
        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id_module');
            $table->bigInteger('id_group')->unsigned();
            $table->foreign('id_group')->references('id_group')->on('groups');
            $table->string('nama');
            $table->string('deskripsi', 255);
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
        Schema::dropIfExists('modules');
    }
};
