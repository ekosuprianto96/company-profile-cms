<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_order_id')->constrained('product_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant')->nullable();
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'shipping_courier_id')) {
                $table->unsignedBigInteger('shipping_courier_id')->nullable()->after('courier');
            }
            if (! Schema::hasColumn('product_orders', 'service_request_id')) {
                $table->unsignedBigInteger('service_request_id')->nullable()->after('shipping_courier_id');
            }
            if (! Schema::hasColumn('product_orders', 'notes')) {
                $table->text('notes')->nullable()->after('service_request_id');
            }
            if (! Schema::hasColumn('product_orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_order_items');
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_courier_id', 'service_request_id', 'notes', 'paid_at']);
        });
    }
};
