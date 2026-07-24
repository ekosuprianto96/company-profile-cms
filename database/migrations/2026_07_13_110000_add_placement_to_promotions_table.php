<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'placement')) {
                // hero  = slider besar paling atas beranda
                // promo = strip pada section promosi
                $table->enum('placement', ['hero', 'promo'])->default('promo')->after('slug');
                $table->index(['placement', 'is_active', 'sort_order']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex(['placement', 'is_active', 'sort_order']);
            $table->dropColumn('placement');
        });
    }
};
