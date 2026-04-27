<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('tax_percentage')->default(0)->after('survey_fee');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('tax_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn(['tax_percentage', 'tax_amount']);
        });
    }
};
