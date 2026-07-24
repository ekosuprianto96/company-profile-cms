<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\HomeSectionAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HomeSectionController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected HomeSectionAdminService $service
    ) {
        $this->setView('admin.pages.mobile.home-sections');
    }

    public function index()
    {
        return $this->view('index', ['sections' => $this->service->list()]);
    }

    public function forms(Request $request)
    {
        try {
            $section = $request->filled('id_home_section')
                ? $this->service->find((int) $request->id_home_section)
                : null;

            $sources = config('home_sections.sources');
            $layouts = config('home_sections.layouts');
            $selectionModes = config('home_sections.selection_modes');
            $autoFilters = config('home_sections.auto_filters');
            $selectedItemIds = $this->service->selectedItemIds($section);

            return $this->setView('admin.components.forms.')->view(
                $request->view,
                compact('section', 'sources', 'layouts', 'selectionModes', 'autoFilters', 'selectedItemIds'),
            );
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /** Opsi item untuk picker manual (dipanggil AJAX saat source dipilih). */
    public function sourceItems(Request $request)
    {
        $source = (string) $request->query('source_type');

        return response()->json(['items' => $this->service->sourceItemOptions($source)]);
    }

    public function store(Request $request)
    {
        try {
            [$data, $itemIds] = $this->validatePayload($request);
            $this->service->create($data, $itemIds);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan section.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            [$data, $itemIds] = $this->validatePayload($request);
            $this->service->update($id, $data, $itemIds);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah section.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_home_section);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus section.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $this->service->reorder((int) $request->id_home_section, $request->direction === 'up' ? 'up' : 'down');
            $this->statusCode = 200;

            return response()->json(['message' => 'Urutan diperbarui.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    /** @return array{0: array<string,mixed>, 1: array<int>} */
    private function validatePayload(Request $request): array
    {
        $sources = array_keys(config('home_sections.sources'));
        $layouts = array_keys(config('home_sections.layouts'));
        $modes = array_keys(config('home_sections.selection_modes'));

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'source_type' => ['required', Rule::in($sources)],
            'layout' => ['required', Rule::in($layouts)],
            'selection_mode' => ['required', Rule::in($modes)],
            'auto_filter' => ['nullable', 'string', 'max:30'],
            'max_items' => ['required', 'integer', 'min:1', 'max:50'],
            'view_all_target' => ['nullable', 'string', 'max:60'],
            'is_active' => ['required', 'boolean'],
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer'],
        ]);

        // auto_filter wajib & valid saat mode otomatis.
        if (($validated['selection_mode'] ?? 'auto') === 'auto') {
            $allowed = array_keys(config("home_sections.auto_filters.{$validated['source_type']}", []));
            if (! in_array($validated['auto_filter'] ?? null, $allowed, true)) {
                throw ValidationException::withMessages([
                    'auto_filter' => ['Filter otomatis tidak valid untuk source ini.'],
                ]);
            }
        }

        $itemIds = $validated['selection_mode'] === 'manual' ? ($validated['item_ids'] ?? []) : [];
        $data = collect($validated)->except(['item_ids'])->toArray();

        return [$data, $itemIds];
    }
}
