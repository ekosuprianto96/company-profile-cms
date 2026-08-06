<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index performa (Batch A) — hasil audit performa 2026-08-05.
 * Menambahkan index pada kolom yang sering di-filter/sort/join tapi belum terindex.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            // FK ditulis unsignedBigInteger polos → tak terindex.
            $table->index('service_request_id', 'product_orders_sr_idx');
            $table->index('shipping_courier_id', 'product_orders_courier_idx');
            // Filter admin daftar order produk.
            $table->index(['status', 'created_at'], 'product_orders_status_created_idx');
            $table->index('payment_status', 'product_orders_paystatus_idx');
        });

        Schema::table('mobile_services', function (Blueprint $table) {
            // Katalog & home: where(is_active) + orderBy(sort_order).
            $table->index(['is_active', 'sort_order'], 'ms_active_sort_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            // Katalog kini filter category_id (taksonomi), bukan product_category_id lama.
            $table->index(['is_active', 'category_id'], 'prod_active_cat_idx');
        });

        Schema::table('visitors', function (Blueprint $table) {
            // Chart & daftar visitor berbasis created_at.
            $table->index('created_at', 'visitors_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropIndex('product_orders_sr_idx');
            $table->dropIndex('product_orders_courier_idx');
            $table->dropIndex('product_orders_status_created_idx');
            $table->dropIndex('product_orders_paystatus_idx');
        });
        Schema::table('mobile_services', fn (Blueprint $table) => $table->dropIndex('ms_active_sort_idx'));
        Schema::table('products', fn (Blueprint $table) => $table->dropIndex('prod_active_cat_idx'));
        Schema::table('visitors', fn (Blueprint $table) => $table->dropIndex('visitors_created_at_idx'));
    }
};
