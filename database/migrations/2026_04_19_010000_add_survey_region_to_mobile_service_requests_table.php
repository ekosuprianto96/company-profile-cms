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
            $table->json('survey_region')->nullable()->after('survey_address');
        });

        DB::table('mobile_service_requests')
            ->whereNull('survey_region')
            ->whereNotNull('draft_payload')
            ->orderBy('id')
            ->chunkById(100, function ($requests) {
                foreach ($requests as $request) {
                    $draftPayload = json_decode((string) $request->draft_payload, true);
                    $surveyRegion = is_array($draftPayload) ? ($draftPayload['surveyRegion'] ?? null) : null;

                    if (! is_array($surveyRegion)) {
                        continue;
                    }

                    DB::table('mobile_service_requests')
                        ->where('id', $request->id)
                        ->update([
                            'survey_region' => json_encode($surveyRegion, JSON_UNESCAPED_UNICODE),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            $table->dropColumn('survey_region');
        });
    }
};
