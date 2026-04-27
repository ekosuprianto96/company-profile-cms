<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->string('transaction_code', 20)->nullable()->after('id')->unique();
        });

        DB::table('mobile_service_requests')
            ->orderBy('id')
            ->chunkById(100, function ($requests) {
                foreach ($requests as $request) {
                    DB::table('mobile_service_requests')
                        ->where('id', $request->id)
                        ->update([
                            'transaction_code' => sprintf('SR-EK%05d', (int) $request->id),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropUnique(['transaction_code']);
            $table->dropColumn('transaction_code');
        });
    }
};
