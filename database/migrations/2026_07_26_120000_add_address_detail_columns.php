<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah "detail alamat" (nomor rumah, blok, patokan, dll) — dipisah dari alamat
 * utama supaya tidak ketimpa reverse-geocode, dan muncul di detail order admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_user_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_user_addresses', 'address_detail')) {
                $table->string('address_detail')->nullable()->after('address');
            }
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'address_detail')) {
                $table->string('address_detail')->nullable()->after('address');
            }
        });

        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'survey_address_detail')) {
                $table->string('survey_address_detail')->nullable()->after('survey_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_user_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_user_addresses', 'address_detail')) {
                $table->dropColumn('address_detail');
            }
        });

        Schema::table('product_orders', function (Blueprint $table) {
            if (Schema::hasColumn('product_orders', 'address_detail')) {
                $table->dropColumn('address_detail');
            }
        });

        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_service_requests', 'survey_address_detail')) {
                $table->dropColumn('survey_address_detail');
            }
        });
    }
};
