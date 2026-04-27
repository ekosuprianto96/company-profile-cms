<?php

namespace App\Services;

use App\Mail\MobileServiceRequestPaymentMethodSelectedMail;
use App\Models\MobileUser;
use App\Models\MobileServiceRequest;
use App\Services\MobileMidtransService;
use App\Services\MobileAppSettingService;
use App\Services\MobileServiceRequestAdminService;
use App\Repositories\MobileBudgetOptionRepository;
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
    protected MobileAppSettingService $mobileAppSettingService,
    protected MobileMidtransService $mobileMidtransService,
    protected MobileServiceRequestAdminService $mobileServiceRequestAdminService,
    protected SystemNotificationService $systemNotificationService,
    ) {}

    public function meta(): array
    {
        $settings = $this->mobileAppSettingService->getSettings();
        $surveyFee = $this->mobileAppSettingService->surveyFee();
        $taxPercentage = $this->mobileAppSettingService->taxPercentage();
        $taxAmount = $this->mobileAppSettingService->taxAmount();

        return [
            'survey_fee' => $surveyFee,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $surveyFee + $taxAmount,
            'survey_coverage' => $this->mobileAppSettingService->surveyCoverage(),
            'payment_methods' => $this->mobileAppSettingService->paymentMethods(),
            'payment_gateway' => $settings['payment_gateway'] ?? [],
            'manual_transfers' => $this->mobileAppSettingService->manualTransfers(),
        ];
    }

    public function createDraft(MobileUser $user, array $payload): MobileServiceRequest
    {
        $storedIssuePhotos = $this->storeIssuePhotos($payload['issue_photos'] ?? []);
        $payload['issue_photos'] = $storedIssuePhotos;
        $normalizedSurveyRegion = $this->normalizeSurveyRegion($payload['survey_region'] ?? null);
        $payload['survey_region'] = $normalizedSurveyRegion;

        $serviceRequest = DB::transaction(function () use ($user, $payload, $normalizedSurveyRegion) {
            $surveyFee = $this->mobileAppSettingService->surveyFee();
            $taxPercentage = $this->mobileAppSettingService->taxPercentage();
            $taxAmount = $this->mobileAppSettingService->taxAmount();
            $totalAmount = $this->mobileAppSettingService->totalAmount();

            $request = $this->mobileServiceRequestRepository->store([
                'mobile_user_id' => $user->id,
                'mobile_service_id' => $payload['mobile_service_id'],
                'mobile_service_need_type_id' => $payload['mobile_service_need_type_id'] ?? null,
                'mobile_budget_option_id' => $payload['mobile_budget_option_id'] ?? null,
                'building_key' => $payload['building_key'],
                'building_label' => $payload['building_label'],
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
            ->loadMissing(['service', 'needType', 'budgetOption', 'handledBy', 'user']);
    }

    public function findForUser(MobileUser $user, int $requestId): ?MobileServiceRequest
    {
        return $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId)?->loadMissing([
            'service',
            'needType',
            'budgetOption',
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

    public function selectPaymentMethod(MobileUser $user, int $requestId, string $paymentMethod): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId);

        if (! $serviceRequest) {
            throw new \Exception('Data pengajuan tidak ditemukan.', 404);
        }

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

        if ($transactionStatus !== 'pending') {
            $this->systemNotificationService->notifyServiceRequestPaymentUpdated(
                $serviceRequest->fresh(['service', 'needType', 'budgetOption', 'user'])
            );
        }

        return $serviceRequest->fresh(['service', 'needType', 'budgetOption', 'user']);
    }
}
