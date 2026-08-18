<?php

namespace App\Services;

use App\Models\MobileService;
use App\Models\MobileServiceRequest;
use App\Models\StepTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Mesin Template Rules Step.
 *
 * - Katalog step core (wajib) / optional / action bersifat TETAP di kode — tanpa CRUD.
 * - Saat pengajuan dibuat, step template layanan di-SNAPSHOT ke
 *   mobile_service_requests.steps_snapshot sehingga checklist menempel ke pengajuan
 *   dan aman dari perubahan template mendadak.
 * - Step core dicentang otomatis oleh event engine (applyEvent); notifikasinya
 *   adalah notifikasi engine yang sudah ada (tidak dobel kirim).
 * - Step optional/custom dicentang otomatis (bila punya trigger) atau manual oleh
 *   admin (completeStep); saat tercentang, action terpilih dieksekusi.
 */
class StepTemplateService
{
    public function __construct(
        protected SystemNotificationService $systemNotificationService,
    ) {}

    /**
     * Step CORE — wajib ada di setiap template, tidak bisa dihapus, key & trigger
     * terkunci. Nama + keterangan boleh diedit admin. Notifikasi step core dikirim
     * oleh engine yang sudah ada (bukan action tambahan).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function coreSteps(): array
    {
        return [
            ['key' => 'draft', 'name' => 'Draft', 'description' => 'Pengajuan tersimpan sebagai draft dan siap diproses.', 'trigger_status' => 'created'],
            ['key' => 'waiting_payment', 'name' => 'Waiting Payment', 'description' => 'User sudah diarahkan ke langkah pembayaran.', 'trigger_status' => 'payment_selected'],
            ['key' => 'review', 'name' => 'Diproses Admin', 'description' => 'Pengajuan sedang ditinjau admin untuk diputuskan.', 'trigger_status' => 'reviewed'],
            ['key' => 'approved', 'name' => 'Disetujui', 'description' => 'Pengajuan disetujui admin dan siap dilanjutkan.', 'trigger_status' => 'approved'],
            ['key' => 'completed', 'name' => 'Completed', 'description' => 'Pengajuan selesai diproses sampai tahap akhir.', 'trigger_status' => 'completed'],
        ];
    }

    /**
     * Step OPTIONAL — disediakan sistem, admin bebas memakai. Trigger terkunci,
     * nama/keterangan/action bisa diatur.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function optionalSteps(): array
    {
        return [
            ['key' => 'proof_uploaded', 'name' => 'Bukti Transfer Diunggah', 'description' => 'Bukti transfer Anda sedang diverifikasi admin.', 'trigger_status' => 'proof_uploaded'],
            ['key' => 'paid', 'name' => 'Pembayaran Berhasil', 'description' => 'Pembayaran telah kami terima.', 'trigger_status' => 'paid'],
        ];
    }

    /**
     * Katalog ACTION — tetap dari sistem, tanpa CRUD. Admin hanya memilih.
     *
     * @return array<string, string> key => label
     */
    public static function actionCatalog(): array
    {
        return [
            'notif_inapp' => 'Notifikasi in-app (user)',
            'notif_push' => 'Push notification (user)',
            'notif_email' => 'Email (user)',
            'notif_sms' => 'SMS (user)',
            'notif_admin' => 'Notifikasi dashboard admin',
        ];
    }

    /**
     * Event engine yang bisa dipakai sebagai trigger step (untuk pilihan di UI
     * template builder). null/kosong = manual oleh admin.
     *
     * @return array<string, string> key => label
     */
    public static function eventCatalog(): array
    {
        return [
            'created' => 'Pengajuan dibuat (draft tersimpan)',
            'payment_selected' => 'User memilih metode pembayaran',
            'proof_uploaded' => 'Bukti transfer diunggah',
            'paid' => 'Pembayaran diterima (lunas)',
            'reviewed' => 'Admin selesai meninjau',
            'approved' => 'Pengajuan disetujui',
            'completed' => 'Pengajuan selesai (completed)',
            'rejected' => 'Pengajuan ditolak',
            'cancelled' => 'Pengajuan dibatalkan user',
        ];
    }

    /**
     * Label action BAWAAN tiap step core (dikirim otomatis oleh engine yang sudah
     * ada — bukan dari katalog action). Hanya untuk ditampilkan di UI builder.
     *
     * @return array<string, string> core step key => label
     */
    public static function coreBuiltinActionLabels(): array
    {
        return [
            'draft' => 'Notif app + email "pengajuan diterima" (otomatis)',
            'waiting_payment' => 'Email instruksi pembayaran (otomatis)',
            'review' => 'Notif app + email keputusan admin (otomatis)',
            'approved' => 'Notif app + email "disetujui" (otomatis)',
            'completed' => 'Notif app + email "selesai" (otomatis)',
        ];
    }

    /**
     * Event yang menyiratkan event lain (mis. completed berarti approved & reviewed
     * juga sudah terjadi) — agar step tak tertinggal saat admin lompat status.
     *
     * @return array<string, array<int, string>>
     */
    protected static function impliedEvents(): array
    {
        return [
            'paid' => ['payment_selected'],
            'approved' => ['reviewed'],
            'rejected' => ['reviewed'],
            'completed' => ['approved', 'reviewed'],
        ];
    }

    /** Template yang berlaku untuk sebuah layanan (template layanan → template default). */
    public function resolveTemplate(?MobileService $service): ?StepTemplate
    {
        $template = $service?->stepTemplate;
        if ($template && $template->is_active) {
            return $template->loadMissing('steps');
        }

        return StepTemplate::query()
            ->with('steps')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Bangun snapshot steps untuk pengajuan baru dari template layanan.
     * Fallback (belum ada template sama sekali): step core bawaan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSnapshot(?MobileService $service): array
    {
        $template = $this->resolveTemplate($service);

        $steps = $template
            ? $template->steps->map(fn ($step) => [
                'key' => $step->key,
                'name' => $step->name,
                'description' => $step->description,
                'kind' => $step->kind,
                'trigger_status' => $step->trigger_status,
                'actions' => array_values($step->actions ?? []),
            ])->all()
            : array_map(fn ($step) => $step + ['kind' => 'core', 'actions' => []], self::coreSteps());

        return array_map(fn ($step) => $step + [
            'completed_at' => null,
            'completed_by' => null,
        ], $steps);
    }

    /** Pasang snapshot ke pengajuan baru (sekali; tidak menimpa yang sudah ada). */
    public function attachSnapshot(MobileServiceRequest $serviceRequest): void
    {
        if (! empty($serviceRequest->steps_snapshot)) {
            return;
        }

        $serviceRequest->forceFill([
            'steps_snapshot' => $this->buildSnapshot($serviceRequest->service),
        ])->save();
    }

    /**
     * Event engine terjadi → centang step yang trigger-nya cocok (termasuk event
     * tersirat) + jalankan action step non-core. Tidak boleh menggagalkan alur
     * utama (pembayaran dsb.) — semua kegagalan hanya dicatat.
     */
    public function applyEvent(MobileServiceRequest $serviceRequest, string $event): void
    {
        try {
            $this->ensureSnapshot($serviceRequest);

            $events = array_merge([$event], self::impliedEvents()[$event] ?? []);
            $steps = $serviceRequest->steps_snapshot ?? [];
            $changed = false;

            foreach ($steps as $index => $step) {
                if (! empty($step['completed_at'])) {
                    continue;
                }
                if (! in_array($step['trigger_status'] ?? null, $events, true)) {
                    continue;
                }

                $steps[$index]['completed_at'] = now()->toIso8601String();
                $steps[$index]['completed_by'] = 'system';
                $changed = true;

                // Action bawaan step core dikirim engine; di sini hanya action
                // TAMBAHAN yang dipilih admin (array actions, default kosong di core).
                $this->runActions($serviceRequest, $steps[$index]);
            }

            if ($changed) {
                $serviceRequest->forceFill(['steps_snapshot' => array_values($steps)])->save();
            }
        } catch (\Throwable $th) {
            Log::warning('Step template applyEvent failed.', [
                'service_request_id' => $serviceRequest->id ?? null,
                'event' => $event,
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Admin mencentang step secara manual (step manual / trigger null, atau
     * mempercepat step apa pun). Action step dieksekusi saat dicentang.
     */
    public function completeStep(MobileServiceRequest $serviceRequest, string $stepKey, ?string $completedBy = null): MobileServiceRequest
    {
        $this->ensureSnapshot($serviceRequest);

        $steps = $serviceRequest->steps_snapshot ?? [];
        $found = false;

        foreach ($steps as $index => $step) {
            if (($step['key'] ?? null) !== $stepKey) {
                continue;
            }

            $found = true;

            if (! empty($step['completed_at'])) {
                break; // sudah tercentang — tidak perlu apa-apa.
            }

            $steps[$index]['completed_at'] = now()->toIso8601String();
            $steps[$index]['completed_by'] = $completedBy ?: 'admin';
            $serviceRequest->forceFill(['steps_snapshot' => array_values($steps)])->save();

            $this->runActions($serviceRequest, $steps[$index]);
            break;
        }

        if (! $found) {
            throw new \Exception('Step tidak ditemukan pada pengajuan ini.', 404);
        }

        return $serviceRequest->refresh();
    }

    /** Batalkan centang step (koreksi kesalahan admin). Tidak memicu action. */
    public function reopenStep(MobileServiceRequest $serviceRequest, string $stepKey): MobileServiceRequest
    {
        $steps = $serviceRequest->steps_snapshot ?? [];

        foreach ($steps as $index => $step) {
            if (($step['key'] ?? null) === $stepKey) {
                $steps[$index]['completed_at'] = null;
                $steps[$index]['completed_by'] = null;
                $serviceRequest->forceFill(['steps_snapshot' => array_values($steps)])->save();

                return $serviceRequest->refresh();
            }
        }

        throw new \Exception('Step tidak ditemukan pada pengajuan ini.', 404);
    }

    /**
     * Pengajuan lama (sebelum fitur ini) belum punya snapshot → bangun dari template
     * layanan dan sinkronkan centang dari timestamp/status yang sudah ada.
     */
    public function ensureSnapshot(MobileServiceRequest $serviceRequest): void
    {
        if (! empty($serviceRequest->steps_snapshot)) {
            return;
        }

        $steps = $this->buildSnapshot($serviceRequest->loadMissing('service')->service);
        $eventTimes = $this->legacyEventTimes($serviceRequest);

        foreach ($steps as $index => $step) {
            $trigger = $step['trigger_status'] ?? null;
            if ($trigger && isset($eventTimes[$trigger])) {
                $steps[$index]['completed_at'] = $eventTimes[$trigger];
                $steps[$index]['completed_by'] = 'system';
            }
        }

        $serviceRequest->forceFill(['steps_snapshot' => array_values($steps)])->save();
    }

    /**
     * Terjemahkan kolom timestamp/status pengajuan lama → waktu tiap event
     * (dengan event tersirat), untuk backfill snapshot.
     *
     * @return array<string, string> event => iso datetime
     */
    protected function legacyEventTimes(MobileServiceRequest $sr): array
    {
        $times = [];
        $set = function (string $event, $time) use (&$times) {
            if ($time) {
                $times[$event] = $time->toIso8601String();
            }
        };

        $set('created', $sr->drafted_at ?? $sr->submitted_at ?? $sr->created_at);
        $set('payment_selected', $sr->payment_method_selected_at);
        $set('proof_uploaded', $sr->payment_proof_uploaded_at);
        $set('paid', $sr->paid_at);
        $set('reviewed', $sr->reviewed_at);
        $set('approved', $sr->approved_at);
        $set('rejected', $sr->rejected_at);
        $set('cancelled', $sr->cancelled_at);

        if ($sr->status === 'completed') {
            $set('completed', $sr->reviewed_at ?? $sr->updated_at);
        }

        // Event tersirat mengikuti waktu event pemicunya.
        foreach (self::impliedEvents() as $event => $implied) {
            if (! isset($times[$event])) {
                continue;
            }
            foreach ($implied as $impliedEvent) {
                $times[$impliedEvent] ??= $times[$event];
            }
        }

        return $times;
    }

    /**
     * Timeline siap-render untuk API/app: state tiap step dihitung di server
     * (done/current/pending) + step terminal Ditolak/Dibatalkan bila terjadi.
     *
     * @return array<int, array<string, mixed>>
     */
    public function timelineFor(MobileServiceRequest $serviceRequest): array
    {
        $this->ensureSnapshot($serviceRequest);

        $steps = collect($serviceRequest->steps_snapshot ?? [])->values();
        $terminated = in_array($serviceRequest->status, ['rejected', 'cancelled'], true);
        $firstPending = $steps->search(fn ($step) => empty($step['completed_at']));

        $timeline = $steps->map(fn ($step, $index) => [
            'key' => $step['key'] ?? ('step_' . $index),
            'name' => $step['name'] ?? '-',
            'description' => $step['description'] ?? null,
            'state' => ! empty($step['completed_at'])
                ? 'done'
                : (! $terminated && $index === $firstPending ? 'current' : 'pending'),
            'completed_at' => $step['completed_at'] ?? null,
            'is_manual' => empty($step['trigger_status']),
            'is_terminal' => false,
        ])->all();

        if ($terminated) {
            $rejected = $serviceRequest->status === 'rejected';
            $timeline[] = [
                'key' => $serviceRequest->status,
                'name' => $rejected ? 'Ditolak' : 'Dibatalkan',
                'description' => $rejected
                    ? ($serviceRequest->rejection_reason ?: 'Pengajuan ditolak admin.')
                    : 'Pengajuan dibatalkan.',
                'state' => 'done',
                'completed_at' => optional($rejected ? $serviceRequest->rejected_at : $serviceRequest->cancelled_at)?->toIso8601String(),
                'is_manual' => false,
                'is_terminal' => true,
            ];
        }

        return $timeline;
    }

    /** Jalankan action step (katalog tetap) — kegagalan tidak menghentikan alur. */
    protected function runActions(MobileServiceRequest $serviceRequest, array $step): void
    {
        $actions = array_values(array_intersect(
            $step['actions'] ?? [],
            array_keys(self::actionCatalog()),
        ));

        if ($actions === []) {
            return;
        }

        try {
            $this->systemNotificationService->notifyServiceRequestStepCompleted(
                $serviceRequest->fresh(['service', 'user']) ?? $serviceRequest,
                $actions,
                (string) ($step['name'] ?? ''),
                (string) ($step['description'] ?? ''),
            );
        } catch (\Throwable $th) {
            Log::warning('Step action dispatch failed.', [
                'service_request_id' => $serviceRequest->id ?? null,
                'step_key' => $step['key'] ?? null,
                'actions' => $actions,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
