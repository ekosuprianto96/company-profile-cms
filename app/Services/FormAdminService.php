<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormAdminService
{
    public function list()
    {
        return Form::withCount(['fields', 'services'])->orderBy('name')->get();
    }

    public function find(int $id): Form
    {
        return Form::with('fields')->findOrFail($id);
    }

    public function create(array $data): Form
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        return Form::create($data);
    }

    public function update(int $id, array $data): Form
    {
        $form = Form::findOrFail($id);
        $data['slug'] = $this->uniqueSlug($data['name'], $form->id);
        $form->update($data);

        return $form;
    }

    public function delete(int $id): bool
    {
        $form = Form::withCount('services')->findOrFail($id);

        if ($form->services_count > 0) {
            throw new \Exception("Form ini masih dipakai {$form->services_count} layanan. Lepas dulu dari layanannya.", 422);
        }

        return (bool) $form->delete();
    }

    /** Duplikat form beserta seluruh field-nya. */
    public function duplicate(int $id): Form
    {
        return DB::transaction(function () use ($id) {
            $source = $this->find($id);
            $copy = Form::create([
                'name' => $source->name . ' (salinan)',
                'slug' => $this->uniqueSlug($source->name . ' salinan'),
                'description' => $source->description,
                'is_active' => false,
            ]);

            foreach ($source->fields as $field) {
                $copy->fields()->create(collect($field->toArray())->except(['id', 'form_id', 'created_at', 'updated_at'])->toArray());
            }

            return $copy;
        });
    }

    // ---------- Field ----------

    public function findField(int $id): FormField
    {
        return FormField::findOrFail($id);
    }

    public function createField(int $formId, array $data): FormField
    {
        $form = Form::findOrFail($formId);
        $data['key'] = $this->uniqueFieldKey($form->id, $data['key'] ?? null, $data['label']);
        $data['sort_order'] = $data['sort_order'] ?? ((int) $form->fields()->max('sort_order') + 1);

        return $form->fields()->create($this->normalizeField($data));
    }

    public function updateField(int $id, array $data): FormField
    {
        $field = $this->findField($id);
        $data['key'] = $this->uniqueFieldKey($field->form_id, $data['key'] ?? null, $data['label'], $field->id);
        $field->update($this->normalizeField($data));

        return $field;
    }

    public function deleteField(int $id): bool
    {
        return (bool) $this->findField($id)->delete();
    }

    /** Geser urutan field satu langkah. */
    public function reorderField(int $id, string $direction): void
    {
        $field = $this->findField($id);
        $isUp = $direction === 'up';

        $neighbor = FormField::where('form_id', $field->form_id)
            ->when($isUp,
                fn ($q) => $q->where('sort_order', '<', $field->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $field->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if (! $neighbor) {
            return;
        }

        DB::transaction(function () use ($field, $neighbor) {
            [$field->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $field->sort_order];
            $field->save();
            $neighbor->save();
        });
    }

    /** Bersihkan payload field sesuai tipenya (buang config yang tidak relevan). */
    private function normalizeField(array $data): array
    {
        $type = $data['type'] ?? 'text';
        $optionTypes = config('form_builder.option_types', []);

        if (! in_array($type, $optionTypes, true)) {
            $data['options_source'] = 'static';
            $data['options_source_key'] = null;
            $data['options'] = null;
        } elseif (($data['options_source'] ?? 'static') === 'datasource') {
            $data['options'] = null;
        } else {
            $data['options_source_key'] = null;
            $data['options'] = collect($data['options'] ?? [])
                ->map(fn ($o) => ['label' => trim((string) ($o['label'] ?? '')), 'value' => trim((string) ($o['value'] ?? ''))])
                ->filter(fn ($o) => $o['label'] !== '')
                ->map(fn ($o) => ['label' => $o['label'], 'value' => $o['value'] !== '' ? $o['value'] : $o['label']])
                ->values()
                ->all();
        }

        // Field tampilan tidak butuh required/validasi.
        if (in_array($type, config('form_builder.display_types', []), true)) {
            $data['is_required'] = false;
            $data['validation'] = null;
        }

        $data['validation'] = ! empty($data['validation']) ? array_filter($data['validation'], fn ($v) => $v !== null && $v !== '') : null;
        $data['conditional'] = ! empty($data['conditional']['field']) ? $data['conditional'] : null;

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'form';
        $slug = $base;
        $i = 2;

        while (Form::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function uniqueFieldKey(int $formId, ?string $key, string $label, ?int $ignoreId = null): string
    {
        $base = Str::snake(Str::slug($key ?: $label, '_')) ?: 'field';
        $final = $base;
        $i = 2;

        while (FormField::where('form_id', $formId)->where('key', $final)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $final = $base . '_' . $i++;
        }

        return $final;
    }
}
