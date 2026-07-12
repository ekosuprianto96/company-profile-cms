<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mock product orders. Placeholder untuk fitur "Order Produk" yang akan
 * dibangun penuh nanti (create produk, keranjang, checkout). Untuk sekarang
 * hanya menyimpan data mock agar mekanisme invoice PDF produk bisa berjalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_user_id')->nullable()->constrained('mobile_users')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('product_name');
            $table->string('variant')->nullable();
            $table->text('image')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('diproses');
            $table->string('status_label')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_orders');
    }
};
