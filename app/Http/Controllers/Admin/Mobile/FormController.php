<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\FormAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected FormAdminService $service
    ) {
        $this->setView('admin.pages.mobile.forms');
    }

    public function index()
    {
        return $this->view('index', ['forms' => $this->service->list()]);
    }

    /** Datasource form builder = registry config + koleksi dinamis (key "collection:{id}"). */
    private function availableDatasources(): array
    {
        $config = config('form_builder.datasources', []);
        $collections = \App\Models\Collection::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($c) => ['collection:' . $c->id => ['label' => 'Koleksi: ' . $c->name]])
            ->all();

        return $config + $collections;
    }

    /** Halaman builder field untuk satu form. */
    public function builder(int $id)
    {
        return $this->view('builder', [
            'form' => $this->service->find($id),
            'fieldTypes' => config('form_builder.field_types'),
            'optionTypes' => config('form_builder.option_types'),
            'displayTypes' => config('form_builder.display_types'),
            'fileTypes' => config('form_builder.file_types'),
            'optionsSources' => config('form_builder.options_sources'),
            'datasources' => $this->availableDatasources(),
            'fieldRoles' => config('form_builder.field_roles'),
        ]);
    }

    /** Pratinjau schema form (tampilan perkiraan di mobile). */
    public function preview(int $id)
    {
        $form = $this->service->find($id);

        return $this->setView('admin.components.forms.')->view('form-preview', [
            'schema' => app(\App\Services\FormSchemaService::class)->schema($form),
        ]);
    }

    public function forms(Request $request)
    {
        try {
            $form = $request->filled('id_form') ? $this->service->find((int) $request->id_form) : null;
            $field = $request->filled('id_form_field') ? $this->service->findField((int) $request->id_form_field) : null;

            // Field lain dalam form yang sama — untuk dropdown "tampil jika".
            $ownerFormId = (int) ($field?->form_id ?: $request->input('form_id'));
            $siblingFields = $ownerFormId
                ? \App\Models\FormField::where('form_id', $ownerFormId)
                    ->when($field, fn ($q) => $q->where('id', '!=', $field->id))
                    ->whereNotIn('type', config('form_builder.display_types', []))
                    ->orderBy('sort_order')->get(['key', 'label'])
                : collect();

            return $this->setView('admin.components.forms.')->view($request->view, [
                'form' => $form,
                'field' => $field,
                'siblingFields' => $siblingFields,
                'formId' => $request->input('form_id'),
                'fieldTypes' => config('form_builder.field_types'),
                'optionTypes' => config('form_builder.option_types'),
                'displayTypes' => config('form_builder.display_types'),
                'fileTypes' => config('form_builder.file_types'),
                'optionsSources' => config('form_builder.options_sources'),
                'datasources' => $this->availableDatasources(),
            ]);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->service->create($this->validateForm($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan form.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $this->service->update($id, $this->validateForm($request));
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah form.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_form);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus form.'], $this->statusCode);
        } catch (\Exception $error) {
            $code = (int) $error->getCode();

            return response()->json(['message' => $error->getMessage()], $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function duplicate(Request $request)
    {
        try {
            $this->service->duplicate((int) $request->id_form);
            $this->statusCode = 200;

            return response()->json(['message' => 'Form berhasil diduplikasi.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    // ---------- Field ----------

    public function storeField(Request $request)
    {
        try {
            [$formId, $data] = $this->validateField($request);
            $this->service->createField($formId, $data);
            $this->statusCode = 200;

            return response()->json(['message' => 'Field ditambahkan.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function updateField(Request $request, int $id)
    {
        try {
            [, $data] = $this->validateField($request, false);
            $this->service->updateField($id, $data);
            $this->statusCode = 200;

            return response()->json(['message' => 'Field diperbarui.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroyField(Request $request)
    {
        try {
            $this->service->deleteField((int) $request->id_form_field);
            $this->statusCode = 200;

            return response()->json(['message' => 'Field dihapus.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function reorderField(Request $request)
    {
        try {
            $this->service->reorderField((int) $request->id_form_field, $request->direction === 'up' ? 'up' : 'down');
            $this->statusCode = 200;

            return response()->json(['message' => 'Urutan diperbarui.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    /** Simpan urutan hasil drag & drop: array id field sesuai urutan baru. */
    public function reorderFields(Request $request)
    {
        $validated = $request->validate([
            'form_id' => ['required', 'integer'],
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        \App\Models\FormField::where('form_id', $validated['form_id'])
            ->whereIn('id', $validated['order'])
            ->get(['id'])
            ->each(function ($field) use ($validated) {
                $position = array_search($field->id, $validated['order'], true);
                if ($position !== false) {
                    $field->update(['sort_order' => $position + 1]);
                }
            });

        return response()->json(['message' => 'Urutan diperbarui.']);
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'show_service_header' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /** @return array{0:int, 1:array<string,mixed>} */
    private function validateField(Request $request, bool $requireForm = true): array
    {
        $types = array_keys(config('form_builder.field_types'));
        $sources = array_keys(config('form_builder.options_sources'));
        $datasources = array_keys($this->availableDatasources());

        // Select "Peran Data" mengirim "" saat tak dipilih → jadikan null.
        $request->merge(['role' => $request->input('role') ?: null]);

        $validated = $request->validate([
            'form_id' => [$requireForm ? 'required' : 'nullable', 'integer', 'exists:forms,id'],
            'key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]*$/i'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($types)],
            'role' => ['nullable', Rule::in(array_keys(config('form_builder.field_roles', [])))],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable', 'boolean'],
            'options_source' => ['nullable', Rule::in($sources)],
            'options_source_key' => ['nullable', Rule::in($datasources)],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['nullable', 'string', 'max:150'],
            'options.*.value' => ['nullable', 'string', 'max:150'],
            'validation' => ['nullable', 'array'],
            'conditional' => ['nullable', 'array'],
        ]);

        // Datasource wajib bila sumber opsi = datasource.
        if (($validated['options_source'] ?? 'static') === 'datasource'
            && in_array($validated['type'], config('form_builder.option_types', []), true)
            && empty($validated['options_source_key'])) {
            throw ValidationException::withMessages(['options_source_key' => ['Pilih sumber data terlebih dahulu.']]);
        }

        $data = collect($validated)->except(['form_id'])->toArray();
        $data['is_required'] = (bool) ($validated['is_required'] ?? false);
        $data['options_source'] = $validated['options_source'] ?? 'static';

        return [(int) ($validated['form_id'] ?? 0), $data];
    }
}
