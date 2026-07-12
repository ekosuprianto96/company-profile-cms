<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            }
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });

        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};
