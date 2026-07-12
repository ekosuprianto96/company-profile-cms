<?php

namespace App\Services;

use App\Mail\MobileServiceRequestPaymentMethodSelectedMail;
use App\Models\MobileUser;
use App\Models\MobileServiceRequest;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Services\VoucherService;
use App\Services\MobileMidtransService;
use App\Services\MobileAppSettingService;
use App\Services\MobileServiceRequestAdminService;
use App\Repositories\MobileBudgetOptionRepository;
use App\Repositories\MobileEventBudgetOptionRepository;
use App\Repositories\MobileEventPackageRepository;
use App\Repositories\MobileEventProjectNeedRepository;
use App\Repositories\MobileEventProjectTypeRepository;
use App\Repositories\MobileServiceRepository;
use App\Repositories\MobileServiceRequestRepository;
use App\Repositories\MobileServiceNeedTypeRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MobileServiceRequestService
{
    public function __construct(
        protected MobileServiceRequestRepository $mobileServiceRequestRepository,
        protected MobileServiceRepository $mobileServiceRepository,
        protected MobileServiceNeedTypeRepository $mobileServiceNeedTypeRepository,
        protected MobileBudgetOptionRepository $mobileBudgetOptionRepository,
        protected MobileEventProjectTypeRepository $mobileEventProjectTypeRepository,
        protected MobileEventProjectNeedRepository $mobileEventProjectNeedRepository,
        protected MobileEventPackageRepository $mobileEventPackageRepository,
        protected MobileEventBudgetOptionRepository $mobileEventBudgetOptionRepository,
        protected MobileAppSettingService $mobileAppSettingService,
        protected MobileMidtransService $mobileMidtransService,
        protected MobileServiceRequestAdminService $mobileServiceRequestAdminService,
        protected SystemNotificationService $systemNotificationService,
        protected VoucherService $voucherService,
    ) {}

    public function meta(): array
    {
        $settings = $this->mobileAppSettingService->getSettings();
        $surveyFee = $this->mobileAppSettingService->surveyFee();
        $eventConsultationFee = $this->mobileAppSettingService->eventConsultationFee();
        $taxPercentage = $this->mobileAppSettingService->taxPercentage();
        $taxAmount = $this->mobileAppSettingService->taxAmount();
        $eventTaxAmount = $this->mobileAppSettingService->eventConsultationTaxAmount();

        return [
            'survey_fee' => $surveyFee,
            'event_consultation_fee' => $eventConsultationFee,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $surveyFee + $taxAmount,
            'event_tax_amount' => $eventTaxAmount,
            'event_total_amount' => $eventConsultationFee + $eventTaxAmount,
            'survey_coverage' => $this->mobileAppSettingService->surveyCoverage(),
            'payment_methods' => $this->mobileAppSettingService->paymentMethods(),
            'payment_gateway' => $settings['payment_gateway'] ?? [],
            'manual_transfers' => $this->mobileAppSettingService->manualTransfers(),
        ];
    }

    public function createDraft(MobileUser $user, array $payload): MobileServiceRequest
    {
        $flowType = $payload['request_flow_type'] ?? 'standard';
        $storedIssuePhotos = $flowType === 'event_project'
            ? []
            : $this->storeIssuePhotos($payload['issue_photos'] ?? []);
        $payload['issue_photos'] = $storedIssuePhotos;
        $normalizedSurveyRegion = $this->normalizeSurveyRegion($payload['survey_region'] ?? null);
        $payload['survey_region'] = $normalizedSurveyRegion;

        $serviceRequest = DB::transaction(function () use ($user, $payload, $normalizedSurveyRegion) {
            $flowType = $payload['request_flow_type'] ?? 'standard';
            $surveyFee = $flowType === 'event_project'
                ? $this->mobileAppSettingService->eventConsultationFee()
                : $this->mobileAppSettingService->surveyFee();
            $taxPercentage = $this->mobileAppSettingService->taxPercentage();
            $taxAmount = (int) round($surveyFee * ($taxPercentage / 100));
            $totalAmount = $surveyFee + $taxAmount;

            $service = $this->mobileServiceRepository->find((int) $payload['mobile_service_id']);
            if (! $service || (($service->request_flow_type ?? 'standard') !== $flowType)) {
                throw new \Exception('Tipe flow pengajuan tidak sesuai dengan layanan yang dipilih.', 422);
            }

            if ($flowType === 'event_project') {
                $this->validateEventSelection($payload);
            }

            $request = $this->mobileServiceRequestRepository->store([
                'mobile_user_id' => $user->id,
                'mobile_service_id' => $payload['mobile_service_id'],
                'mobile_service_need_type_id' => $flowType === 'event_project' ? null : ($payload['mobile_service_need_type_id'] ?? null),
                'mobile_budget_option_id' => $flowType === 'event_project' ? null : ($payload['mobile_budget_option_id'] ?? null),
                'request_flow_type' => $flowType,
                'mobile_event_project_type_id' => $flowType === 'event_project' ? $payload['mobile_event_project_type_id'] : null,
                'mobile_event_project_need_id' => $flowType === 'event_project' ? $payload['mobile_event_project_need_id'] : null,
                'mobile_event_package_id' => $flowType === 'event_project' ? $payload['mobile_event_package_id'] : null,
                'mobile_event_budget_option_id' => $flowType === 'event_project' ? $payload['mobile_event_budget_option_id'] : null,
                'event_date' => $flowType === 'event_project' ? $payload['event_date'] : null,
                'building_key' => $flowType === 'event_project' ? null : ($payload['building_key'] ?? null),
                'building_label' => $flowType === 'event_project' ? null : ($payload['building_label'] ?? null),
                'description' => $payload['description'] ?? null,
                'issue_photos' => $payload['issue_photos'],
                'survey_address' => $payload['survey_address'],
                'survey_region' => $normalizedSurveyRegion,
                'survey_latitude' => $payload['survey_latitude'],
                'survey_longitude' => $payload['survey_longitude'],
                'survey_date' => $payload['survey_date'],
                'survey_fee' => $surveyFee,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'draft_payload' => $payload,
                'drafted_at' => now(),
                'submitted_at' => now(),
            ]);

            $request->forceFill([
                'transaction_code' => sprintf('SR-EK%05d', (int) $request->id),
            ])->save();

            $freshRequest = $request->fresh([
                'service',
                'needType',
                'budgetOption',
                'eventProjectType',
                'eventProjectNeed',
                'eventPackage',
                'eventBudgetOption',
                'user',
            ]);

            return $freshRequest;
        });

        try {
            $this->mobileServiceRequestAdminService->notifySubmitted($serviceRequest);
            $this->systemNotificationService->notifyServiceRequestCreated($serviceRequest);
        } catch (\Throwable $th) {
            Log::warning('Mobile service request notification failed after draft saved.', [
                'message' => $th->getMessage(),
                'stack' => $th->getTraceAsString(),
                'service_request_id' => $serviceRequest->id ?? null,
                'service_request_code' => $serviceRequest->transaction_code_label ?? null,
            ]);
        }

        return $serviceRequest;
    }

    /**
     * @return Collection<int, MobileServiceRequest>
     */
    public function listForUser(MobileUser $user): Collection
    {
        return $this->mobileServiceRequestRepository->listByUser($user->id)
            ->loadMissing(['service', 'needType', 'budgetOption', 'eventProjectType', 'eventProjectNeed', 'eventPackage', 'eventBudgetOption', 'handledBy', 'user']);
    }

    public function findForUser(MobileUser $user, int $requestId): ?MobileServiceRequest
    {
        return $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId)?->loadMissing([
            'service',
            'needType',
            'budgetOption',
            'eventProjectType',
            'eventProjectNeed',
            'eventPackage',
            'eventBudgetOption',
            'handledBy',
            'user',
        ]);
    }

    /**
     * @param  mixed  $surveyRegion
     * @return array<string, mixed>|null
     */
    protected function normalizeSurveyRegion($surveyRegion): ?array
    {
        if (is_array($surveyRegion)) {
            return $surveyRegion;
        }

        if (! is_string($surveyRegion) || trim($surveyRegion) === '') {
            return null;
        }

        $decoded = json_decode($surveyRegion, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<int, mixed>  $issuePhotos
     * @return array<int, array<string, mixed>>
     */
    protected function storeIssuePhotos(array $issuePhotos): array
    {
        if ($issuePhotos === []) {
            return [];
        }

        $storedPhotos = [];

        foreach ($issuePhotos as $photo) {
            if ($photo instanceof UploadedFile) {
                $fileName = now()->format('Y-m-d') . '-' . $photo->hashName();
                $path = $photo->storeAs('mobile/service-requests', $fileName, 'public');

                $storedPhotos[] = [
                    'file_name' => $fileName,
                    'mime_type' => $photo->getMimeType(),
                    'path' => $path,
                    'uri' => Storage::disk('public')->url($path),
                    'size' => $photo->getSize(),
                ];

                continue;
            }

            if (is_array($photo)) {
                $path = (string) ($photo['path'] ?? $photo['uri'] ?? '');
                $url = (string) ($photo['url'] ?? '');

                if ($path === '' && $url !== '') {
                    $path = $url;
                }

                if ($path === '') {
                    continue;
                }

                $storedPhotos[] = [
                    'file_name' => $photo['file_name'] ?? basename($path),
                    'mime_type' => $photo['mime_type'] ?? $photo['mimeType'] ?? null,
                    'path' => $path,
                    'uri' => $url ?: $path,
                    'size' => $photo['size'] ?? null,
                ];
            }
        }

        return $storedPhotos;
    }

    public function selectPaymentMethod(MobileUser $user, int $requestId, string $paymentMethod, ?int $voucherId = null): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId);

        if (! $serviceRequest) {
            throw new \Exception('Data pengajuan tidak ditemukan.', 404);
        }

        // Rekonsiliasi voucher: lepas voucher lama (jika ada), lalu terapkan yang baru bila dipilih.
        $this->releaseVoucher($serviceRequest);
        if ($voucherId) {
            $this->applyVoucher($serviceRequest, $user, $voucherId);
        }
        $serviceRequest->refresh();

        $serviceRequest->update([
            'payment_method' => $paymentMethod,
            'payment_gateway_provider' => $paymentMethod === 'manual_transfer' ? null : 'midtrans',
            'payment_status' => $paymentMethod === 'manual_transfer' ? 'waiting_transfer' : 'pending',
            'status' => $paymentMethod === 'manual_transfer' ? 'waiting_transfer' : 'waiting_payment',
            'payment_method_selected_at' => now(),
        ]);

        $paymentData = null;

        if ($paymentMethod !== 'manual_transfer') {
            $paymentData = $this->mobileMidtransService->createSnapTransaction($serviceRequest->fresh(['user']), $paymentMethod);

            $serviceRequest->update([
                'midtrans_order_id' => $paymentData['order_id'],
                'midtrans_snap_token' => $paymentData['token'] ?? null,
                'midtrans_redirect_url' => $paymentData['redirect_url'] ?? null,
                'payment_reference' => $paymentData['order_id'],
                'payment_payload' => $paymentData['raw'] ?? null,
            ]);
        }

        $freshServiceRequest = $serviceRequest->fresh([
            'service',
            'needType',
            'budgetOption',
            'eventProjectType',
            'eventProjectNeed',
            'eventPackage',
            'eventBudgetOption',
            'user',
        ]);

        if ($freshServiceRequest?->user?->email) {
            Mail::to($freshServiceRequest->user->email)
                ->cc(config('mail.from.address'))
                ->queue(new MobileServiceRequestPaymentMethodSelectedMail($freshServiceRequest));
        }

        if ($paymentData) {
            $freshServiceRequest->setAttribute('payment_data', $paymentData);
        }

        return $freshServiceRequest;
    }

    /** Tandai redemption reserved order ini sebagai used (bayar sukses) atau released (gagal/batal). */
    public function settleVoucherRedemption(MobileServiceRequest $serviceRequest, string $paymentStatus): void
    {
        $query = VoucherRedemption::query()
            ->where('order_type', 'service')
            ->where('order_id', $serviceRequest->id)
            ->where('status', 'reserved');

        if ($paymentStatus === 'paid') {
            $query->update(['status' => 'used', 'used_at' => now()]);
        } elseif (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
            $query->update(['status' => 'released', 'released_at' => now()]);
        }
    }

    /** Lepas voucher yang masih reserved untuk order ini & kembalikan total ke nilai dasar. */
    protected function releaseVoucher(MobileServiceRequest $serviceRequest): void
    {
        VoucherRedemption::query()
            ->where('order_type', 'service')
            ->where('order_id', $serviceRequest->id)
            ->where('status', 'reserved')
            ->update(['status' => 'released', 'released_at' => now()]);

        $surveyFee = (int) $serviceRequest->survey_fee;
        $taxPercentage = (int) $serviceRequest->tax_percentage;
        $taxAmount = (int) round($surveyFee * $taxPercentage / 100);

        $serviceRequest->update([
            'voucher_id' => null,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'total_amount' => $surveyFee + $taxAmount,
        ]);
    }

    /** Validasi + reserve voucher, lalu terapkan diskon ke total order (diskon di subtotal pra-pajak). */
    protected function applyVoucher(MobileServiceRequest $serviceRequest, MobileUser $user, int $voucherId): void
    {
        // Kunci baris voucher agar cek kuota + reserve atomik (hindari race kuota).
        DB::transaction(function () use ($serviceRequest, $user, $voucherId) {
            $voucher = Voucher::query()
                ->with('targetItems')
                ->where('order_type', 'service')
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('user_scope', 'all')
                    ->orWhereHas('targetUsers', fn ($u) => $u->where('mobile_users.id', $user->id)))
                ->whereKey($voucherId)
                ->lockForUpdate()
                ->first();

            if (! $voucher) {
                throw new \Exception('Voucher tidak tersedia.', 422);
            }

            $subtotal = (int) $serviceRequest->survey_fee;
            $itemId = (int) $serviceRequest->mobile_service_id;

            $reason = $this->voucherService->ineligibilityReason($voucher, $user, 'service', $subtotal, $itemId);
            if ($reason) {
                throw new \Exception($reason, 422);
            }

            $discount = $this->voucherService->calculateDiscount($voucher, $subtotal);
            $discountedSubtotal = max(0, $subtotal - $discount);
            $taxPercentage = (int) $serviceRequest->tax_percentage;
            $taxAmount = (int) round($discountedSubtotal * $taxPercentage / 100);

            VoucherRedemption::create([
                'voucher_id' => $voucher->id,
                'mobile_user_id' => $user->id,
                'order_type' => 'service',
                'order_id' => $serviceRequest->id,
                'base_amount' => $subtotal,
                'discount_amount' => $discount,
                'status' => 'reserved',
                'reserved_at' => now(),
            ]);

            $serviceRequest->update([
                'voucher_id' => $voucher->id,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'total_amount' => $discountedSubtotal + $taxAmount,
            ]);
        });
    }

    public function cancel(MobileUser $user, int $requestId): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId);

        if (! $serviceRequest) {
            throw new \Exception('Data pengajuan tidak ditemukan.', 404);
        }

        if (! $serviceRequest->canBeCancelled()) {
            throw new \Exception('Pengajuan tidak dapat dibatalkan karena sudah diproses admin atau biaya survey sudah dibayar.', 422);
        }

        // Lepas voucher yang di-reserve agar kuota kembali.
        $this->settleVoucherRedemption($serviceRequest, 'cancelled');

        $serviceRequest->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $serviceRequest->fresh([
            'service',
            'needType',
            'budgetOption',
            'eventProjectType',
            'eventProjectNeed',
            'eventPackage',
            'eventBudgetOption',
            'user',
        ]) ?? $serviceRequest;
    }

    public function handleMidtransNotification(array $payload): ?MobileServiceRequest
    {
        $notification = $this->mobileMidtransService->handleNotification($payload);
        $orderId = (string) ($notification['order_id'] ?? '');

        if ($orderId === '') {
            throw new \Exception('Order ID notifikasi Midtrans kosong.');
        }

        $serviceRequest = $this->mobileServiceRequestRepository->findByMidtransOrderId($orderId);

        if (! $serviceRequest) {
            throw new \Exception('Pengajuan Midtrans tidak ditemukan.');
        }

        $transactionStatus = (string) ($notification['transaction_status'] ?? '');
        $fraudStatus = (string) ($notification['fraud_status'] ?? '');
        $paymentType = (string) ($notification['payment_type'] ?? '');

        $paymentStatus = match ($transactionStatus) {
            'settlement', 'capture' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => $serviceRequest->payment_status,
        };

        if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
            $paymentStatus = 'challenge';
        }

        $serviceRequest->update([
            'payment_status' => $paymentStatus,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_payment_type' => $paymentType ?: null,
            'paid_at' => in_array($paymentStatus, ['paid'], true) ? now() : $serviceRequest->paid_at,
            'payment_payload' => array_merge($serviceRequest->payment_payload ?? [], [
                'midtrans_notification' => $notification,
            ]),
        ]);

        $this->settleVoucherRedemption($serviceRequest, $paymentStatus);

        if ($transactionStatus !== 'pending') {
            $this->systemNotificationService->notifyServiceRequestPaymentUpdated(
                $serviceRequest->fresh(['service', 'needType', 'budgetOption', 'eventProjectType', 'eventProjectNeed', 'eventPackage', 'eventBudgetOption', 'user'])
            );
        }

        return $serviceRequest->fresh(['service', 'needType', 'budgetOption', 'eventProjectType', 'eventProjectNeed', 'eventPackage', 'eventBudgetOption', 'user']);
    }

    private function validateEventSelection(array $payload): void
    {
        $type = $this->mobileEventProjectTypeRepository->find((int) $payload['mobile_event_project_type_id']);
        $need = $this->mobileEventProjectNeedRepository->find((int) $payload['mobile_event_project_need_id']);
        $package = $this->mobileEventPackageRepository->find((int) $payload['mobile_event_package_id']);
        $budget = $this->mobileEventBudgetOptionRepository->find((int) $payload['mobile_event_budget_option_id']);

        if (! $type?->is_active || ! $need?->is_active || ! $package?->is_active || ! $budget?->is_active) {
            throw new \Exception('Pilihan event tidak aktif.', 422);
        }

        if ((int) $need->mobile_event_project_type_id !== (int) $type->id) {
            throw new \Exception('Kebutuhan event tidak sesuai dengan jenis project.', 422);
        }

        if ((int) $package->mobile_event_project_need_id !== (int) $need->id) {
            throw new \Exception('Paket event tidak sesuai dengan kebutuhan project.', 422);
        }
    }
}
