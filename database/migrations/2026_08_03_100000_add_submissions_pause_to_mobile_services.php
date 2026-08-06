<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            $table->boolean('submissions_paused')->default(false)->after('is_coming_soon');
            $table->text('submissions_paused_note')->nullable()->after('submissions_paused');
            $table->timestamp('submissions_paused_at')->nullable()->after('submissions_paused_note');
            $table->unsignedBigInteger('submissions_paused_by')->nullable()->after('submissions_paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_services', function (Blueprint $table) {
            $table->dropColumn(['submissions_paused', 'submissions_paused_note', 'submissions_paused_at', 'submissions_paused_by']);
        });
    }
};
