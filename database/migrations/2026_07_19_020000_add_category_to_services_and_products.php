<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Layanan & produk memakai kategori master (bisa di level mana pun dari
        // pohon). nullOnDelete: bila kategori (daun) dihapus, tautannya dilepas.
        Schema::table('mobile_services', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_services', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('id')
                    ->constrained('categories')->nullOnDelete();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('id')
                    ->constrained('categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_services', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
