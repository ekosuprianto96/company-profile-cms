<?php

namespace App\Services;

use App\Models\MobileService;
use App\Models\MobileUser;
use App\Models\MobileServiceRequest;
use App\Models\Proposal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Menerima isian form dinamis dan menyimpannya sebagai satu Proposal
 * (jawaban + snapshot schema + snapshot harga layanan + total).
 * Validasi dibangun dari schema form, bukan dari daftar field yang di-hardcode.
 */
class ProposalService
{
    public function __construct(
        protected FormSchemaService $formSchema,
        protected SystemNotificationService $notifications,
    ) {}

    public function submit(MobileUser $user, MobileService $service, array $answers): Proposal
    {
        $service->loadMissing(['form.fields', 'priceItems']);
        // Layanan tanpa form dari builder memakai form standar generik, agar
        // seluruh layanan aktif konsisten dapat diajukan (selaras formSchema API).
        $schema = $service->form
            ? $this->formSchema->schema($service->form)
            : $this->formSchema->defaultSchema($service);

        $fields = $schema['fields'];
        $answers = $this->stripInactive($fields, $answers);

        $this->validateAnswers($fields, $answers);

        $priceItems = $service->priceItems->map(fn ($item) => [
            'type' => $item->type,
            'label' => $item->label,
            'amount' => (int) $item->amount,
            'is_required' => (bool) $item->is_required,
        ])->values()->all();

        $total = collect($priceItems)->where('is_required', true)->sum('amount');

        $proposal = Proposal::create([
            'proposal_number' => $this->generateNumber(),
            'mobile_user_id' => $user->id,
            'mobile_service_id' => $service->id,
            'form_id' => $service->form_id,
            'status' => 'submitted',
            'answers' => $answers,
            'form_snapshot' => $schema,
            'price_items' => $priceItems,
            'total_amount' => (int) $total,
            'submitted_at' => Carbon::now(),
        ]);

        $this->syncServiceRequest($proposal, $service, $user, $fields, $answers);

        // Notifikasi pengajuan baru (pesan lengkap dgn data proposal) ke user + admin.
        $serviceRequest = $proposal->serviceRequest()->with(['user', 'service'])->first();
        if ($serviceRequest) {
            $this->notifications->notifyProposalSubmitted($proposal, $serviceRequest);
        }

        return $proposal->fresh();
    }

    /**
     * Buat pengajuan (mobile_service_requests) yang tertaut ke proposal, supaya
     * alur pembayaran/invoice/operasional yang sudah berjalan tetap dipakai.
     * Pemetaan memakai kunci konvensi dari form; field yang tidak ada dilewati.
     */
    private function syncServiceRequest(Proposal $proposal, MobileService $service, MobileUser $user, array $fields, array $answers): void
    {
        $typeOf = [];
        $keyByRole = [];   // peran field (admin) → key
        foreach ($fields as $field) {
            $typeOf[$field['key']] = $field['type'];
            if (! empty($field['role'])) {
                $keyByRole[$field['role']] = $field['key'];
            }
        }

        // Ambil jawaban berdasarkan PERAN dulu; kalau tak ada peran, fallback tipe/kunci.
        $answerByRole = fn (string $role) => isset($keyByRole[$role]) ? ($answers[$keyByRole[$role]] ?? null) : null;
        $firstAnswerOfType = function (string $type) use ($answers, $typeOf) {
            foreach ($answers as $key => $value) {
                if (($typeOf[$key] ?? null) === $type) {
                    return $value;
                }
            }
            return null;
        };

        // Lokasi: peran survey_location → field location pertama.
        $location = $answerByRole('survey_location');
        if (! is_array($location)) {
            $location = $firstAnswerOfType('location');
        }
        $location = is_array($location) ? $location : [];

        // Tanggal: peran survey_date → survey_date → field date pertama.
        $date = $answerByRole('survey_date') ?? ($answers['survey_date'] ?? null);
        if (! $date) {
            $date = $firstAnswerOfType('date');
        }

        // Foto kondisi: peran issue_photos → gabungan semua field image.
        $photos = $answerByRole('issue_photos');
        if (! is_array($photos)) {
            $photos = [];
            foreach ($answers as $key => $value) {
                if (($typeOf[$key] ?? null) === 'image' && is_array($value)) {
                    $photos = array_merge($photos, $value);
                }
            }
        }

        // building_type / description: peran → fallback kunci konvensi.
        $buildingVal = $answerByRole('building_type') ?? ($answers['building_type'] ?? null);
        $descriptionVal = $answerByRole('description') ?? ($answers['description'] ?? null);

        $fee = (int) $proposal->total_amount;
        $taxPercentage = (int) app(MobileAppSettingService::class)->taxPercentage();
        $tax = (int) round($fee * ($taxPercentage / 100));

        $request = MobileServiceRequest::create([
            'mobile_user_id' => $user->id,
            'mobile_service_id' => $service->id,
            'proposal_id' => $proposal->id,
            'request_flow_type' => $service->request_flow_type ?? 'standard',
            // Jenis kebutuhan & budget kini dari Collection; nilainya tampil dari
            // jawaban proposal (readableAnswers), bukan lagi kolom FK modul lama.
            'building_label' => is_string($buildingVal) ? $buildingVal : null,
            'description' => is_string($descriptionVal) ? $descriptionVal : null,
            'issue_photos' => $photos,
            'survey_address' => $location['address'] ?? null,
            'survey_region' => $location['region'] ?? null,
            'survey_latitude' => $location['latitude'] ?? null,
            'survey_longitude' => $location['longitude'] ?? null,
            'survey_date' => $date,
            'survey_fee' => $fee,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $tax,
            'products_amount' => 0,
            'total_amount' => $fee + $tax,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'draft_payload' => $answers,
            'drafted_at' => Carbon::now(),
            'submitted_at' => Carbon::now(),
        ]);

        $request->forceFill(['transaction_code' => sprintf('SR-EK%05d', (int) $request->id)])->save();
    }


    /** Field yang kondisinya tidak terpenuhi tidak ikut disimpan & tidak divalidasi. */
    public function stripInactive(array $fields, array $answers): array
    {
        $displayTypes = config('form_builder.display_types', []);
        $kept = [];

        foreach ($fields as $field) {
            if (in_array($field['type'], $displayTypes, true)) {
                continue; // section/note tidak menyimpan jawaban
            }
            if (! $this->isActive($field, $answers)) {
                continue;
            }
            if (array_key_exists($field['key'], $answers)) {
                $kept[$field['key']] = $answers[$field['key']];
            }
        }

        return $kept;
    }

    public function isActive(array $field, array $answers): bool
    {
        $cond = $field['conditional'] ?? null;
        if (! $cond || empty($cond['field'])) {
            return true;
        }

        $actual = $answers[$cond['field']] ?? null;
        $expected = $cond['value'] ?? null;

        return match ($cond['operator'] ?? 'eq') {
            'neq' => $this->normalize($actual) !== $this->normalize($expected),
            'filled' => ! in_array($actual, [null, '', []], true),
            default => $this->normalize($actual) === $this->normalize($expected),
        };
    }

    private function normalize($value): string
    {
        return is_array($value) ? json_encode($value) : trim((string) $value);
    }

    /** Bangun rules Laravel dari schema, lalu validasi jawaban. */
    public function validateAnswers(array $fields, array $answers): void
    {
        $rules = [];
        $attributes = [];
        $displayTypes = config('form_builder.display_types', []);

        foreach ($fields as $field) {
            if (in_array($field['type'], $displayTypes, true) || ! $this->isActive($field, $answers)) {
                continue;
            }

            $key = $field['key'];
            $val = (array) ($field['validation'] ?? []);
            $required = ! empty($field['is_required']);
            $rule = [$required ? 'required' : 'nullable'];
            $optionValues = collect($field['options'] ?? [])->pluck('value')->map(fn ($v) => (string) $v)->all();

            switch ($field['type']) {
                case 'number':
                    $rule[] = 'numeric';
                    if (isset($val['min'])) $rule[] = 'min:' . $val['min'];
                    if (isset($val['max'])) $rule[] = 'max:' . $val['max'];
                    break;
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'date':
                case 'datetime':
                    $rule[] = 'date';
                    break;
                case 'checkbox':
                    $rule[] = 'boolean';
                    break;
                case 'select':
                case 'radio':
                    $rule[] = 'string';
                    if ($optionValues) $rule[] = 'in:' . implode(',', $optionValues);
                    break;
                case 'multiselect':
                case 'checkbox_group':
                    $rule[] = 'array';
                    $rules["{$key}.*"] = $optionValues ? ['string', 'in:' . implode(',', $optionValues)] : ['string'];
                    break;
                case 'image':
                case 'file':
                    // Berkas sudah diunggah lebih dulu; yang dikirim adalah path/URL.
                    $rule[] = 'array';
                    $rules["{$key}.*"] = ['string', 'max:500'];
                    if (! empty($val['max_files'])) $rule[] = 'max:' . (int) $val['max_files'];
                    break;
                case 'location':
                    $rule[] = 'array';
                    $rules["{$key}.latitude"] = [$required ? 'required' : 'nullable', 'numeric'];
                    $rules["{$key}.longitude"] = [$required ? 'required' : 'nullable', 'numeric'];
                    $rules["{$key}.address"] = [$required ? 'required' : 'nullable', 'string', 'max:5000'];
                    $rules["{$key}.region"] = ['nullable', 'string', 'max:5000'];
                    break;
                default: // text, textarea, phone, time
                    $rule[] = 'string';
                    if (isset($val['min_length'])) $rule[] = 'min:' . $val['min_length'];
                    if (isset($val['max_length'])) $rule[] = 'max:' . $val['max_length'];
            }

            $rules[$key] = $rule;
            $attributes[$key] = $field['label'];
        }

        Validator::make($answers, $rules, [], $attributes)->validate();
    }

    /**
     * Jawaban dalam bentuk siap-tampil (admin & PDF): pakai snapshot schema agar
     * label tetap sesuai kondisi saat proposal dikirim.
     * @return array<int, array{label:string,type:string,value:string,files:array}>
     */
    public function readableAnswers(\App\Models\Proposal $proposal): array
    {
        $answers = $proposal->answers ?? [];
        $fields = $proposal->snapshotFields();
        $display = config('form_builder.display_types', []);
        $out = [];

        foreach ($fields as $field) {
            $type = $field['type'];
            if (in_array($type, $display, true)) {
                $out[] = ['label' => $field['label'], 'type' => 'section', 'value' => '', 'files' => []];
                continue;
            }

            if (! array_key_exists($field['key'], $answers)) {
                continue;
            }

            $raw = $answers[$field['key']];
            $files = [];
            $value = '';

            switch ($type) {
                case 'multiselect':
                case 'checkbox_group':
                    $value = implode(', ', array_map(fn ($v) => $this->optionLabel($field, $v), (array) $raw));
                    break;
                case 'select':
                case 'radio':
                    $value = $this->optionLabel($field, $raw);
                    break;
                case 'checkbox':
                    $value = $raw ? 'Ya' : 'Tidak';
                    break;
                case 'location':
                    $loc = (array) $raw;
                    $value = trim(($loc['address'] ?? '') . (isset($loc['region']) && $loc['region'] ? ' — ' . $loc['region'] : ''));
                    if (isset($loc['latitude'], $loc['longitude'])) {
                        $value .= ' (' . $loc['latitude'] . ', ' . $loc['longitude'] . ')';
                    }
                    break;
                case 'image':
                case 'file':
                    $files = array_map(fn ($p) => ['path' => $p, 'name' => basename((string) $p), 'url' => storageUrl($p)], (array) $raw);
                    $value = count($files) . ' berkas';
                    break;
                default:
                    $value = is_array($raw) ? implode(', ', $raw) : (string) $raw;
            }

            $out[] = ['label' => $field['label'], 'type' => $type, 'value' => $value, 'files' => $files];
        }

        return $out;
    }

    private function optionLabel(array $field, $value): string
    {
        foreach ($field['options'] ?? [] as $option) {
            if ((string) $option['value'] === (string) $value) {
                return (string) $option['label'];
            }
        }

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    private function generateNumber(): string
    {
        $prefix = 'PRP-' . Carbon::now()->format('Ymd');
        $todayCount = Proposal::where('proposal_number', 'like', $prefix . '%')->count();

        do {
            $number = $prefix . '-' . str_pad((string) (++$todayCount), 4, '0', STR_PAD_LEFT);
        } while (Proposal::where('proposal_number', $number)->exists());

        return $number;
    }
}
