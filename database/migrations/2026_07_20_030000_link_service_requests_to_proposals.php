<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('mobile_service_requests', 'proposal_id')) {
                $table->foreignId('proposal_id')->nullable()->after('mobile_service_id')
                    ->constrained('proposals')->nullOnDelete();
            }

            // Tidak semua layanan memerlukan survei (mis. IT Developer), jadi data
            // survei tidak lagi wajib sejak form pengajuan menjadi dinamis.
            $table->text('survey_address')->nullable()->change();
            $table->decimal('survey_latitude', 10, 7)->nullable()->change();
            $table->decimal('survey_longitude', 10, 7)->nullable()->change();
            $table->date('survey_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('mobile_service_requests', 'proposal_id')) {
                $table->dropConstrainedForeignId('proposal_id');
            }
        });
    }
};
