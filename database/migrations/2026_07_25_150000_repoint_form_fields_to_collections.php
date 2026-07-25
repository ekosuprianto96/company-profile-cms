<?php

use App\Models\Collection;
use App\Models\FormField;
use Illuminate\Database\Migrations\Migration;

/**
 * Fase 2: alihkan field form builder yang memakai datasource modul lama
 * ke Collection hasil migrasi (key "collection:{id}").
 */
return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'need_types' => 'jenis-kebutuhan',
            'budget_options' => 'opsi-budget',
            'event_project_types' => 'tipe-proyek-event',
            'event_project_needs' => 'kebutuhan-proyek-event',
            'event_packages' => 'paket-event',
            'event_budget_options' => 'opsi-budget-event',
        ];

        foreach ($map as $oldKey => $slug) {
            $collection = Collection::where('slug', $slug)->first();
            if (! $collection) {
                continue;
            }

            FormField::where('options_source', 'datasource')
                ->where('options_source_key', $oldKey)
                ->update(['options_source_key' => 'collection:' . $collection->id]);
        }
    }

    public function down(): void
    {
        // Tidak dibalik otomatis (pemetaan maju).
    }
};
