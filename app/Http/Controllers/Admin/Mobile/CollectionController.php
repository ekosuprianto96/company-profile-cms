<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionEntry;
use App\Models\CollectionField;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CollectionController extends Controller
{
    use AdminView;

    public const FIELD_TYPES = [
        'text' => 'Teks',
        'textarea' => 'Teks Panjang',
        'number' => 'Angka',
        'boolean' => 'Ya / Tidak',
        'select' => 'Pilihan',
    ];

    public function __construct()
    {
        $this->setView('admin.pages.mobile.collections');
    }

    public function index()
    {
        return $this->view('index', [
            'collections' => Collection::withCount(['fields', 'entries'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);
            $data['slug'] = $this->uniqueSlug($data['name']);
            $collection = Collection::create($data);

            return response()->json(['message' => 'Koleksi dibuat.', 'id' => $collection->id]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $e->errors()], 422);
        }
    }

    public function manage(int $id)
    {
        $collection = Collection::with(['fields', 'entries'])->findOrFail($id);

        return $this->view('manage', [
            'collection' => $collection,
            'fieldTypes' => self::FIELD_TYPES,
        ]);
    }

    public function update(Request $request, int $id)
    {
        try {
            $collection = Collection::findOrFail($id);
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
                'label_field' => ['nullable', 'string'],
                'is_active' => ['required', 'boolean'],
            ]);
            $collection->update($data);

            return response()->json(['message' => 'Koleksi diperbarui.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy(Request $request)
    {
        Collection::findOrFail((int) $request->id)->delete();

        return response()->json(['message' => 'Koleksi dihapus.']);
    }

    // ---------- Field (skema) ----------

    public function storeField(Request $request)
    {
        try {
            $data = $this->validateField($request);
            CollectionField::create($data);

            return response()->json(['message' => 'Field ditambahkan.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $e->errors()], 422);
        }
    }

    public function updateField(Request $request, int $id)
    {
        try {
            $field = CollectionField::findOrFail($id);
            $data = $this->validateField($request, $field);
            $field->update($data);

            return response()->json(['message' => 'Field diperbarui.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $e->errors()], 422);
        }
    }

    public function destroyField(Request $request)
    {
        CollectionField::findOrFail((int) $request->id)->delete();

        return response()->json(['message' => 'Field dihapus.']);
    }

    private function validateField(Request $request, ?CollectionField $field = null): array
    {
        $collectionId = (int) ($field?->collection_id ?: $request->input('collection_id'));

        $validated = $request->validate([
            'collection_id' => [$field ? 'nullable' : 'required', 'integer', 'exists:collections,id'],
            'label' => ['required', 'string', 'max:120'],
            'key' => [
                'required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('collection_fields', 'key')->where('collection_id', $collectionId)->ignore($field?->id),
            ],
            'type' => ['required', Rule::in(array_keys(self::FIELD_TYPES))],
            'is_required' => ['nullable', 'boolean'],
            'options_text' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ], [
            'key.regex' => 'Key harus huruf kecil, angka, atau underscore, dan diawali huruf.',
        ]);

        $options = null;
        if ($validated['type'] === 'select' && ! empty($validated['options_text'])) {
            $options = collect(preg_split('/\r?\n/', $validated['options_text']))
                ->map(fn ($o) => trim($o))->filter()->values()->all();
        }

        return [
            'collection_id' => $collectionId,
            'label' => $validated['label'],
            'key' => $validated['key'],
            'type' => $validated['type'],
            'options' => $options,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    // ---------- Entry (data) ----------

    public function storeEntry(Request $request)
    {
        $collection = Collection::with('fields')->findOrFail((int) $request->input('collection_id'));
        [$data, $errors] = $this->buildEntryData($collection, $request->input('data', []));
        if ($errors) {
            return response()->json(['message' => 'Data belum lengkap.', 'errors' => $errors], 422);
        }

        $collection->entries()->create([
            'data' => $data,
            'is_active' => (bool) $request->input('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return response()->json(['message' => 'Data ditambahkan.']);
    }

    public function updateEntry(Request $request, int $id)
    {
        $entry = CollectionEntry::with('collection.fields')->findOrFail($id);
        [$data, $errors] = $this->buildEntryData($entry->collection, $request->input('data', []));
        if ($errors) {
            return response()->json(['message' => 'Data belum lengkap.', 'errors' => $errors], 422);
        }

        $entry->update([
            'data' => $data,
            'is_active' => (bool) $request->input('is_active', $entry->is_active),
        ]);

        return response()->json(['message' => 'Data diperbarui.']);
    }

    public function destroyEntry(Request $request)
    {
        CollectionEntry::findOrFail((int) $request->id)->delete();

        return response()->json(['message' => 'Data dihapus.']);
    }

    /** Susun data entry dari input mentah, coerce per tipe field + cek wajib. */
    private function buildEntryData(Collection $collection, array $raw): array
    {
        $data = [];
        $errors = [];
        foreach ($collection->fields as $field) {
            $value = $raw[$field->key] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            if ($field->type === 'number') {
                $value = ($value === '' || $value === null) ? null : (int) preg_replace('/[^0-9\-]/', '', (string) $value);
            } elseif ($field->type === 'boolean') {
                $value = in_array(strtolower((string) $value), ['1', 'ya', 'true', 'on'], true);
            }

            if ($field->is_required && ($value === null || $value === '' || $value === false)) {
                $errors["data.{$field->key}"] = ["{$field->label} wajib diisi."];
            }
            $data[$field->key] = $value;
        }

        return [$data, $errors];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'koleksi';
        $slug = $base;
        $i = 1;
        while (Collection::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
