<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Syarat & ketentuan (rich text dari CKEditor).
        Schema::table('vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('vouchers', 'terms')) {
                $table->longText('terms')->nullable()->after('description');
            }
        });

        // Klaim voucher per user — voucher harus diambil dulu sebelum bisa dipakai.
        Schema::create('voucher_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('mobile_user_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->timestamp('claimed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['voucher_id', 'mobile_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_claims');
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'terms')) {
                $table->dropColumn('terms');
            }
        });
    }
};
