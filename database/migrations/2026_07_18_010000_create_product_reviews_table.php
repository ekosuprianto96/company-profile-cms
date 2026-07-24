<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_order_id')->constrained('product_orders')->cascadeOnDelete();
            // Item spesifik yang dinilai; nullOnDelete supaya review tetap ada
            // untuk agregat produk walau baris item pesanan dibersihkan.
            $table->foreignId('product_order_item_id')->nullable()->constrained('product_order_items')->nullOnDelete();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5, wajib
            $table->text('comment')->nullable();   // opsional
            $table->timestamps();

            // Satu review per produk per pesanan (mencegah nilai ganda).
            $table->unique(['product_order_id', 'product_id'], 'product_reviews_order_product_unique');
            // Daftar review per produk (detail produk) diurut terbaru.
            $table->index(['product_id', 'created_at']);
        });

        // Agregat untuk ditampilkan di kartu/detail produk. Kolom `rating` sudah
        // ada; `review_count` menyimpan jumlah ulasan agar tak dihitung tiap query.
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'review_count')) {
                $table->unsignedInteger('review_count')->default(0)->after('rating');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'review_count')) {
                $table->dropColumn('review_count');
            }
        });
    }
};
