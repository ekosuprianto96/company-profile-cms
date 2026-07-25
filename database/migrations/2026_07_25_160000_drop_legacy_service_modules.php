<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2: hapus modul lama (need types, budget options, event *) yang kini
 * digantikan Collection. Drop kolom FK di service request + tabel + pivot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_service_requests', function (Blueprint $table) {
            foreach ([
                'mobile_service_need_type_id', 'mobile_budget_option_id',
                'mobile_event_project_type_id', 'mobile_event_project_need_id',
                'mobile_event_package_id', 'mobile_event_budget_option_id',
            ] as $col) {
                if (Schema::hasColumn('mobile_service_requests', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
            if (Schema::hasColumn('mobile_service_requests', 'event_date')) {
                $table->dropColumn('event_date');
            }
        });

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mobile_service_need_type_relations');
        Schema::dropIfExists('mobile_event_budget_options');
        Schema::dropIfExists('mobile_event_packages');
        Schema::dropIfExists('mobile_event_project_needs');
        Schema::dropIfExists('mobile_event_project_types');
        Schema::dropIfExists('mobile_budget_options');
        Schema::dropIfExists('mobile_service_need_types');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Tidak dibalik: modul lama sudah dipensiunkan (data ada di Collection).
    }
};
