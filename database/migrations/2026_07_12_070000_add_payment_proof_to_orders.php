<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'payment_proof_path')) {
                $table->text('payment_proof_path')->nullable()->after('payment_reference');
            }
            if (! Schema::hasColumn('mobile_service_requests', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof_path');
            }
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'payment_proof_path')) {
                $table->text('payment_proof_path')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('product_orders', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'payment_proof_uploaded_at']);
        });
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'payment_proof_uploaded_at']);
        });
    }
};
