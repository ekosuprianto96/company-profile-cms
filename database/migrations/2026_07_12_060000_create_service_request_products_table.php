<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_service_request_id')->constrained('mobile_service_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'products_amount')) {
                $table->unsignedBigInteger('products_amount')->default(0)->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_products');
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn('products_amount');
        });
    }
};
