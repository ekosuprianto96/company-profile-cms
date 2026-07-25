<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mekanisme banned untuk user mobile: banned_at (kapan), ban_reason (alasan),
 * banned_by (admin pelaku). User dianggap banned bila banned_at tidak null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('mobile_users', 'ban_reason')) {
                $table->string('ban_reason', 500)->nullable()->after('banned_at');
            }
            if (! Schema::hasColumn('mobile_users', 'banned_by')) {
                $table->foreignId('banned_by')->nullable()->after('ban_reason')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_users', 'banned_by')) {
                $table->dropConstrainedForeignId('banned_by');
            }
            foreach (['ban_reason', 'banned_at'] as $col) {
                if (Schema::hasColumn('mobile_users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
