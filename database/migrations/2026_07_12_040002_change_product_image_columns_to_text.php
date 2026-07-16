<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('primary_image')->nullable()->change();
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->text('path')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('primary_image')->nullable()->change();
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('path')->change();
        });
    }
};
