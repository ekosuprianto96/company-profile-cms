<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileServiceRequest;
use App\Models\StepTemplate;
use App\Models\StepTemplateStep;
use App\Services\StepTemplateService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD Template Rules Step (dashboard admin).
 * Step core wajib: tidak bisa dihapus, key & trigger terkunci — nama/keterangan/
 * action tambahan tetap bisa diedit. Katalog action & event TETAP dari sistem.
 */
class StepTemplateController extends Controller
{
    use AdminView;

    public function __construct(
        protected StepTemplateService $stepTemplateService,
    ) {
        $this->setView('admin.pages.mobile.step-templates');
    }

    public function index()
    {
        return $this->view('index', [
            'templates' => StepTemplate::withCount(['steps', 'services'])->orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    /** Halaman builder step untuk satu template. */
    public function builder(int $id)
    {
        $template = StepTemplate::with('steps')->findOrFail($id);

        $usedKeys = $template->steps->pluck('key')->all();

        return $this->view('builder', [
            'template' => $template,
            'actionCatalog' => StepTemplateService::actionCatalog(),
            'eventCatalog' => StepTemplateService::eventCatalog(),
            'builtinLabels' => StepTemplateService::coreBuiltinActionLabels(),
            // Step optional yang belum dipakai template ini (bisa ditambahkan admin).
            'availableOptional' => array_values(array_filter(
                StepTemplateService::optionalSteps(),
                fn ($step) => ! in_array($step['key'], $usedKeys, true),
            )),
            'missingCore' => array_values(array_filter(
                StepTemplateService::coreSteps(),
                fn ($step) => ! in_array($step['key'], $usedKeys, true),
            )),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);

            $template = DB::transaction(function () use ($data) {
                $template = StepTemplate::create($data + ['is_active' => true]);

                // Step core WAJIB otomatis terpasang di template baru.
                foreach (StepTemplateService::coreSteps() as $index => $step) {
                    $template->steps()->create($step + ['kind' => 'core', 'actions' => [], 'sort_order' => $index]);
                }

                return $template;
            });

            return response()->json(['message' => 'Template berhasil dibuat.', 'id' => $template->id]);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $template = StepTemplate::findOrFail($id);
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
                'is_active' => ['nullable', 'boolean'],
                'is_default' => ['nullable', 'boolean'],
            ]);

            if (! empty($data['is_default'])) {
                StepTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
            } elseif ($template->is_default && array_key_exists('is_default', $data)) {
                // Template default tidak boleh dilepas tanpa pengganti.
                $data['is_default'] = true;
            }

            $template->update($data);

            return response()->json(['message' => 'Template diperbarui.']);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $template = StepTemplate::withCount('services')->findOrFail((int) $request->id);

            if ($template->is_default) {
                return response()->json(['message' => 'Template default tidak bisa dihapus. Jadikan template lain sebagai default terlebih dulu.'], 422);
            }
            if ($template->services_count > 0) {
                return response()->json(['message' => "Template masih dipakai {$template->services_count} layanan. Lepas dulu dari layanannya."], 422);
            }

            $template->delete();

            return response()->json(['message' => 'Template dihapus.']);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function duplicate(Request $request)
    {
        try {
            $source = StepTemplate::with('steps')->findOrFail((int) $request->id);

            $copy = DB::transaction(function () use ($source) {
                $copy = StepTemplate::create([
                    'name' => $source->name . ' (Salinan)',
                    'description' => $source->description,
                    'is_default' => false,
                    'is_active' => true,
                ]);

                foreach ($source->steps as $step) {
                    $copy->steps()->create($step->only(['key', 'name', 'description', 'kind', 'trigger_status', 'actions', 'sort_order']));
                }

                return $copy;
            });

            return response()->json(['message' => 'Template diduplikasi.', 'id' => $copy->id]);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // ================= STEP =================

    /** Tambah step: optional (dari katalog) atau custom (buatan admin). */
    public function storeStep(Request $request, int $templateId)
    {
        try {
            $template = StepTemplate::with('steps')->findOrFail($templateId);

            $data = $request->validate([
                'source' => ['required', 'in:core,optional,custom'],
                'key' => ['nullable', 'string', 'max:60'],
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
                'trigger_status' => ['nullable', 'string', 'in:' . implode(',', array_keys(StepTemplateService::eventCatalog()))],
                'actions' => ['nullable', 'array'],
                'actions.*' => ['string', 'in:' . implode(',', array_keys(StepTemplateService::actionCatalog()))],
            ]);

            if ($data['source'] === 'core') {
                // Pulihkan step wajib yang hilang (template lama/impor).
                $core = collect(StepTemplateService::coreSteps())->firstWhere('key', $data['key']);
                if (! $core) {
                    return response()->json(['message' => 'Step core tidak dikenal.'], 422);
                }
                $key = $core['key'];
                $kind = 'core';
                $trigger = $core['trigger_status'];
            } elseif ($data['source'] === 'optional') {
                $optional = collect(StepTemplateService::optionalSteps())->firstWhere('key', $data['key']);
                if (! $optional) {
                    return response()->json(['message' => 'Step optional tidak dikenal.'], 422);
                }
                $key = $optional['key'];
                $kind = 'optional';
                $trigger = $optional['trigger_status']; // trigger optional terkunci dari katalog
            } else {
                $key = 'custom_' . \Illuminate\Support\Str::slug($data['name'], '_');
                $kind = 'custom';
                $trigger = $data['trigger_status'] ?? null; // null = dicentang manual admin
            }

            if ($template->steps->contains('key', $key)) {
                return response()->json(['message' => 'Step dengan identitas yang sama sudah ada di template ini.'], 422);
            }

            $template->steps()->create([
                'key' => $key,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'kind' => $kind,
                'trigger_status' => $trigger,
                'actions' => array_values($data['actions'] ?? []),
                'sort_order' => ((int) $template->steps->max('sort_order')) + 1,
            ]);

            return response()->json(['message' => 'Step ditambahkan.']);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function updateStep(Request $request, int $stepId)
    {
        try {
            $step = StepTemplateStep::findOrFail($stepId);

            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:1000'],
                'trigger_status' => ['nullable', 'string', 'in:' . implode(',', array_keys(StepTemplateService::eventCatalog()))],
                'actions' => ['nullable', 'array'],
                'actions.*' => ['string', 'in:' . implode(',', array_keys(StepTemplateService::actionCatalog()))],
            ]);

            $payload = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'actions' => array_values($data['actions'] ?? []),
            ];

            // Trigger hanya bisa diubah pada step custom; core & optional terkunci.
            if ($step->kind === 'custom') {
                $payload['trigger_status'] = $data['trigger_status'] ?? null;
            }

            $step->update($payload);

            return response()->json(['message' => 'Step diperbarui.']);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function destroyStep(Request $request)
    {
        try {
            $step = StepTemplateStep::findOrFail((int) $request->id);

            if ($step->kind === 'core') {
                return response()->json(['message' => 'Step wajib bawaan sistem tidak bisa dihapus.'], 422);
            }

            $step->delete();

            return response()->json(['message' => 'Step dihapus.']);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /** Drag & drop urutan step. */
    public function reorderSteps(Request $request, int $templateId)
    {
        try {
            $order = array_values(array_map('intval', (array) $request->input('order', [])));

            foreach ($order as $index => $stepId) {
                StepTemplateStep::where('step_template_id', $templateId)
                    ->where('id', $stepId)
                    ->update(['sort_order' => $index]);
            }

            return response()->json(['message' => 'Urutan step disimpan.']);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    // ================= PROGRESS DI PENGAJUAN =================

    /** Admin mencentang step pengajuan secara manual → action step dieksekusi. */
    public function completeRequestStep(Request $request, int $requestId)
    {
        try {
            $serviceRequest = MobileServiceRequest::with(['service', 'user'])->findOrFail($requestId);
            $stepKey = (string) $request->input('step_key');

            $this->stepTemplateService->completeStep($serviceRequest, $stepKey, $request->user()?->name ?? 'admin');

            return response()->json(['message' => 'Step ditandai selesai. Action step telah dijalankan.']);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $error->getCode() === 404 ? 404 : 500);
        }
    }

    /** Batalkan centang step (koreksi). Tidak memicu action. */
    public function reopenRequestStep(Request $request, int $requestId)
    {
        try {
            $serviceRequest = MobileServiceRequest::findOrFail($requestId);
            $stepKey = (string) $request->input('step_key');

            $this->stepTemplateService->reopenStep($serviceRequest, $stepKey);

            return response()->json(['message' => 'Centang step dibatalkan.']);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $error->getCode() === 404 ? 404 : 500);
        }
    }
}
