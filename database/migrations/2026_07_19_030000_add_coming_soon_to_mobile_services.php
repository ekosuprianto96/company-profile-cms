<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_services', 'is_coming_soon')) {
                $table->boolean('is_coming_soon')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_services', 'is_coming_soon')) {
                $table->dropColumn('is_coming_soon');
            }
        });
    }
};
