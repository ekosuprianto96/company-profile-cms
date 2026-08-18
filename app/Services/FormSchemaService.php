<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use App\Models\MobileService;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah Form (hasil form builder) menjadi schema siap-render untuk mobile.
 * Opsi field yang memakai datasource di-resolve di sini, jadi aplikasi mobile
 * cukup membaca `options` tanpa tahu asal datanya.
 */
class FormSchemaService
{
    /** cache kolom per tabel agar tidak query berulang */
    private array $columnCache = [];

    public function schema(?Form $form): ?array
    {
        if (! $form) {
            return null;
        }

        $form->loadMissing('fields');

        return [
            'id' => $form->id,
            'name' => $form->name,
            'slug' => $form->slug,
            'description' => $form->description,
            'show_service_header' => (bool) $form->show_service_header,
            'fields' => $form->fields->map(fn (FormField $field) => $this->field($field))->values()->all(),
        ];
    }

    /**
     * Form generik untuk layanan yang belum punya form dari form builder.
     * Semua layanan aktif kini memakai alur form dinamis; bila admin belum
     * membuat form khusus, layanan tetap bisa diajukan lewat form standar ini.
     * Peran field selaras dengan yang dibaca ProposalService (building_type,
     * description, survey_location, survey_date).
     */
    public function defaultSchema(MobileService $service): array
    {
        $field = fn (array $attrs) => array_merge([
            'key' => $attrs['key'],
            'label' => $attrs['label'],
            'type' => $attrs['type'],
            'role' => $attrs['role'] ?? null,
            'placeholder' => $attrs['placeholder'] ?? null,
            'help_text' => $attrs['help_text'] ?? null,
            'is_required' => $attrs['is_required'] ?? false,
            'options' => $attrs['options'] ?? [],
            'validation' => (object) [],
            'conditional' => null,
        ], []);

        return [
            'id' => 0,
            'name' => 'Pengajuan ' . $service->title,
            'slug' => 'default-' . $service->slug,
            'description' => 'Lengkapi detail kebutuhan dan jadwal survei untuk mengajukan layanan ini.',
            'show_service_header' => true,
            'fields' => [
                $field(['key' => 'sec_kebutuhan', 'label' => 'Detail Kebutuhan', 'type' => 'section']),
                $field([
                    'key' => 'building_type', 'label' => 'Jenis Bangunan', 'type' => 'select', 'role' => 'building_type',
                    'is_required' => true, 'placeholder' => 'Pilih jenis bangunan',
                    'options' => [
                        ['label' => 'Rumah', 'value' => 'rumah'],
                        ['label' => 'Ruko', 'value' => 'ruko'],
                        ['label' => 'Kantor', 'value' => 'kantor'],
                        ['label' => 'Gedung', 'value' => 'gedung'],
                        ['label' => 'Lainnya', 'value' => 'lainnya'],
                    ],
                ]),
                $field([
                    'key' => 'description', 'label' => 'Deskripsi Kebutuhan', 'type' => 'textarea', 'role' => 'description',
                    'is_required' => true, 'placeholder' => "Contoh: Renovasi dapur 3×4 m — ganti keramik lantai & dinding, pasang kitchen set.\nSebutkan jenis pekerjaan, ukuran/luas area, dan material yang diinginkan.\nCeritakan kondisi saat ini dan hasil akhir yang Anda harapkan.\nSemakin detail (lokasi, kisaran budget, target waktu), makin akurat estimasi tim kami.",
                ]),
                $field(['key' => 'sec_survei', 'label' => 'Lokasi & Jadwal Survei', 'type' => 'section']),
                $field([
                    'key' => 'survey_location', 'label' => 'Lokasi Survei', 'type' => 'location', 'role' => 'survey_location',
                    'is_required' => true, 'help_text' => 'Pilih lokasi dari peta agar surveyor mudah menemukan.',
                ]),
                $field([
                    'key' => 'survey_date', 'label' => 'Tanggal Survei', 'type' => 'date', 'role' => 'survey_date',
                    'is_required' => true,
                ]),
            ],
        ];
    }

    public function field(FormField $field): array
    {
        return [
            'key' => $field->key,
            'label' => $field->label,
            'type' => $field->type,
            'role' => $field->role,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'is_required' => (bool) $field->is_required,
            'options' => $this->resolveOptions($field),
            'validation' => $field->validation ?: (object) [],
            'conditional' => $field->conditional ?: null,
            'config' => $field->config ?: null,
        ];
    }

    /** Opsi final: dari datasource (master data) atau daftar manual. */
    public function resolveOptions(FormField $field): array
    {
        if (! $field->hasOptions()) {
            return [];
        }

        if ($field->options_source === 'datasource') {
            return $this->datasourceOptions($field->options_source_key);
        }

        return collect($field->options ?? [])
            ->map(fn ($option) => [
                'label' => (string) ($option['label'] ?? $option['value'] ?? ''),
                'value' => $option['value'] ?? ($option['label'] ?? ''),
            ])
            ->filter(fn ($option) => $option['label'] !== '')
            ->values()
            ->all();
    }

    /** Ambil opsi dari master data sesuai registry config/form_builder.php. */
    public function datasourceOptions(?string $key): array
    {
        // Datasource dinamis: koleksi buatan admin. Key format "collection:{id}".
        if ($key && str_starts_with($key, 'collection:')) {
            return $this->collectionOptions((int) substr($key, strlen('collection:')));
        }

        $config = $key ? config("form_builder.datasources.{$key}") : null;

        if (! $config || ! class_exists($config['model'])) {
            return [];
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $config['model']();
        $table = $model->getTable();
        $query = $config['model']::query();

        if ($this->hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }
        if ($this->hasColumn($table, 'sort_order')) {
            $query->orderBy('sort_order');
        }
        $query->orderBy($config['text']);

        return $query->get()
            ->map(fn ($row) => [
                'label' => (string) $row->{$config['text']},
                'value' => $row->{$config['value']},
            ])
            ->values()
            ->all();
    }

    /** Opsi dari koleksi dinamis: value = ID entry, label = field label koleksi. */
    public function collectionOptions(int $collectionId): array
    {
        $collection = \App\Models\Collection::with('fields')->find($collectionId);
        if (! $collection) {
            return [];
        }

        $labelKey = $collection->labelFieldKey();

        return $collection->entries()->where('is_active', true)->get()
            ->map(function (\App\Models\CollectionEntry $entry) use ($labelKey) {
                $label = $labelKey ? ($entry->data[$labelKey] ?? '') : '';
                if ($label === '' || $label === null) {
                    // Fallback: nilai non-kosong pertama.
                    $label = collect($entry->data ?? [])->first(fn ($v) => $v !== null && $v !== '') ?? ('Entry #' . $entry->id);
                }

                return ['label' => (string) $label, 'value' => $entry->id];
            })
            ->values()
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $this->columnCache[$table] ??= Schema::getColumnListing($table);

        return in_array($column, $this->columnCache[$table], true);
    }
}
