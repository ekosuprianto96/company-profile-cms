<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->string('payment_gateway_provider', 50)->nullable()->after('payment_method');
            $table->string('midtrans_order_id')->nullable()->after('payment_gateway_provider');
            $table->string('midtrans_snap_token')->nullable()->after('midtrans_order_id');
            $table->text('midtrans_redirect_url')->nullable()->after('midtrans_snap_token');
            $table->string('midtrans_transaction_status', 50)->nullable()->after('midtrans_redirect_url');
            $table->string('midtrans_payment_type', 50)->nullable()->after('midtrans_transaction_status');
            $table->timestamp('paid_at')->nullable()->after('payment_method_selected_at');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway_provider',
                'midtrans_order_id',
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'midtrans_transaction_status',
                'midtrans_payment_type',
                'paid_at',
            ]);
        });
    }
};
