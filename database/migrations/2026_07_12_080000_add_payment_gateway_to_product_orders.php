<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $columns = [
                'payment_gateway_provider' => fn () => $table->string('payment_gateway_provider')->nullable()->after('payment_method'),
                'midtrans_order_id' => fn () => $table->string('midtrans_order_id')->nullable()->after('payment_gateway_provider'),
                'midtrans_snap_token' => fn () => $table->text('midtrans_snap_token')->nullable()->after('midtrans_order_id'),
                'midtrans_redirect_url' => fn () => $table->text('midtrans_redirect_url')->nullable()->after('midtrans_snap_token'),
                'midtrans_transaction_status' => fn () => $table->string('midtrans_transaction_status')->nullable()->after('midtrans_redirect_url'),
                'midtrans_payment_type' => fn () => $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_status'),
                'payment_reference' => fn () => $table->string('payment_reference')->nullable()->after('midtrans_payment_type'),
                'payment_payload' => fn () => $table->json('payment_payload')->nullable()->after('payment_reference'),
                'payment_method_selected_at' => fn () => $table->timestamp('payment_method_selected_at')->nullable()->after('payment_proof_uploaded_at'),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::hasColumn('product_orders', $name)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway_provider',
                'midtrans_order_id',
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'midtrans_transaction_status',
                'midtrans_payment_type',
                'payment_reference',
                'payment_payload',
                'payment_method_selected_at',
            ]);
        });
    }
};
