<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'voucher_id')) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('mobile_service_requests', 'discount_amount')) {
                $table->unsignedBigInteger('discount_amount')->default(0)->after('voucher_id');
            }
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'voucher_id')) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('grand_total');
            }
            if (! Schema::hasColumn('product_orders', 'discount_amount')) {
                $table->unsignedBigInteger('discount_amount')->default(0)->after('voucher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn(['voucher_id', 'discount_amount']);
        });

        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn(['voucher_id', 'discount_amount']);
        });
    }
};
