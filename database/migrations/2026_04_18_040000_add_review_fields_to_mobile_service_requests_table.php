<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('drafted_at');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('rejected_at');
            $table->text('admin_note')->nullable()->after('handled_by_user_id');
            $table->text('rejection_reason')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handled_by_user_id');
            $table->dropColumn([
                'submitted_at',
                'reviewed_at',
                'approved_at',
                'rejected_at',
                'admin_note',
                'rejection_reason',
            ]);
        });
    }
};
