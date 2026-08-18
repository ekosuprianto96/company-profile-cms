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
use App\Repositories\MobileServiceRepository;
use App\Repositories\MobileServiceRequestRepository;
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
        protected MobileAppSettingService $mobileAppSettingService,
        protected MobileMidtransService $mobileMidtransService,
        protected MobileServiceRequestAdminService $mobileServiceRequestAdminService,
        protected SystemNotificationService $systemNotificationService,
        protected VoucherService $voucherService,
        protected StepTemplateService $stepTemplateService,
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

            $service = $this->mobileServiceRepository->find((int) $payload['mobile_service_id']);
            if (! $service || (($service->request_flow_type ?? 'standard') !== $flowType)) {
                throw new \Exception('Tipe flow pengajuan tidak sesuai dengan layanan yang dipilih.', 422);
            }

            $resolvedProducts = $this->resolveAddOnProducts($payload['products'] ?? [], (int) $payload['mobile_service_id']);
            $productsAmount = array_sum(array_map(fn ($item) => $item['subtotal'], $resolvedProducts));
            $totalAmount = $surveyFee + $taxAmount + $productsAmount;

            $request = $this->mobileServiceRequestRepository->store([
                'mobile_user_id' => $user->id,
                'mobile_service_id' => $payload['mobile_service_id'],
                'request_flow_type' => $flowType,
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
                'products_amount' => $productsAmount,
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

            foreach ($resolvedProducts as $item) {
                $request->products()->create($item);
            }

            $freshRequest = $request->fresh(['service', 'user', 'products.product']);

            return $freshRequest;
        });

        // Snapshot Template Rules Step menempel ke pengajuan + centang step "created".
        $this->stepTemplateService->applyEvent($serviceRequest, 'created');

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
            ->loadMissing(['service', 'handledBy', 'user', 'products.product']);
    }

    public function findForUser(MobileUser $user, int $requestId): ?MobileServiceRequest
    {
        return $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId)?->loadMissing([
            'service',
            'handledBy',
            'user',
            'products.product',
            'proposal',
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
                    'uri' => storageUrl($path),
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

        $this->stepTemplateService->applyEvent($serviceRequest, 'payment_selected');

        $freshServiceRequest = $serviceRequest->fresh([
            'service',
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

    /** Upload bukti transfer manual oleh user; order masuk antrean verifikasi admin. */
    public function uploadPaymentProof(MobileUser $user, int $requestId, UploadedFile $file): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findByUserAndId($user->id, $requestId);

        if (! $serviceRequest) {
            throw new \Exception('Data pengajuan tidak ditemukan.', 404);
        }

        if ($serviceRequest->payment_method !== 'manual_transfer') {
            throw new \Exception('Order ini tidak menggunakan pembayaran transfer manual.', 422);
        }

        if ($serviceRequest->payment_status === 'paid' || $serviceRequest->paid_at) {
            throw new \Exception('Pembayaran sudah lunas.', 422);
        }

        $fileName = now()->format('Y-m-d') . '-' . \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('mobile/payment-proofs', $fileName, 'public');

        if ($serviceRequest->payment_proof_path) {
            Storage::disk('public')->delete($serviceRequest->payment_proof_path);
        }

        $serviceRequest->update([
            'payment_proof_path' => $path,
            'payment_proof_uploaded_at' => now(),
            'payment_status' => 'waiting_verification',
            'status' => 'waiting_verification',
        ]);

        $this->stepTemplateService->applyEvent($serviceRequest, 'proof_uploaded');

        return $serviceRequest->fresh(['service', 'user', 'products.product']) ?? $serviceRequest;
    }

    /** Admin menyetujui bukti transfer manual: tandai lunas + settle voucher jadi used. */
    public function confirmManualPayment(int $requestId): MobileServiceRequest
    {
        $serviceRequest = $this->findOrFailById($requestId);

        if ($serviceRequest->payment_method !== 'manual_transfer') {
            throw new \Exception('Order ini tidak menggunakan pembayaran transfer manual.', 422);
        }

        if ($serviceRequest->payment_status === 'paid') {
            throw new \Exception('Pembayaran sudah dikonfirmasi.', 422);
        }

        $serviceRequest->update([
            'payment_status' => 'paid',
            'status' => 'waiting_payment' === $serviceRequest->status ? 'pending' : $serviceRequest->status,
            'paid_at' => now(),
        ]);

        $this->settleVoucherRedemption($serviceRequest, 'paid');
        $this->stepTemplateService->applyEvent($serviceRequest, 'paid');

        $fresh = $serviceRequest->fresh(['service', 'user', 'products.product']) ?? $serviceRequest;
        $this->systemNotificationService->notifyServiceRequestPaymentUpdated($fresh);

        return $fresh;
    }

    /** Admin menolak bukti transfer manual: kembalikan ke status menunggu transfer. */
    public function rejectManualPayment(int $requestId, ?string $reason = null): MobileServiceRequest
    {
        $serviceRequest = $this->findOrFailById($requestId);

        $serviceRequest->update([
            'payment_status' => 'waiting_transfer',
            'status' => 'waiting_transfer',
            'payment_proof_path' => null,
            'payment_proof_uploaded_at' => null,
            'admin_note' => $reason ?: $serviceRequest->admin_note,
        ]);

        $fresh = $serviceRequest->fresh(['service', 'user', 'products.product']) ?? $serviceRequest;
        $this->systemNotificationService->notifyServiceRequestPaymentUpdated($fresh);

        return $fresh;
    }

    protected function findOrFailById(int $requestId): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findForAdmin($requestId);

        if (! $serviceRequest) {
            throw new \Exception('Data pengajuan tidak ditemukan.', 404);
        }

        return $serviceRequest;
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
        $productsAmount = (int) $serviceRequest->products_amount;

        $serviceRequest->update([
            'voucher_id' => null,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'total_amount' => $surveyFee + $taxAmount + $productsAmount,
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
            $productsAmount = (int) $serviceRequest->products_amount;

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
                'total_amount' => $discountedSubtotal + $taxAmount + $productsAmount,
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

        $this->stepTemplateService->applyEvent($serviceRequest, 'cancelled');

        return $serviceRequest->fresh([
            'service',
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

        if ($paymentStatus === 'paid') {
            $this->stepTemplateService->applyEvent($serviceRequest, 'paid');
        }

        if ($transactionStatus !== 'pending') {
            $this->systemNotificationService->notifyServiceRequestPaymentUpdated(
                $serviceRequest->fresh(['service', 'user', 'products.product'])
            );
        }

        return $serviceRequest->fresh(['service', 'user', 'products.product']);
    }

    /**
     * Validasi & normalisasi produk tambahan (add-on) pada pengajuan.
     * Harga diambil dari database (server-authoritative), bukan dari client.
     * Produk hanya diterima bila aktif dan cocok dengan layanan (service_scope=all
     * atau terpetakan ke layanan tersebut via product_service).
     *
     * @param  array<int, array{product_id?: mixed, quantity?: mixed}>  $rawProducts
     * @return array<int, array{product_id: int, product_name: string, unit_price: int, quantity: int, subtotal: int}>
     */
    private function resolveAddOnProducts(array $rawProducts, int $serviceId): array
    {
        if (empty($rawProducts)) {
            return [];
        }

        // Gabungkan quantity bila produk yang sama dikirim lebih dari sekali.
        $quantities = [];
        foreach ($rawProducts as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
        }

        if (empty($quantities)) {
            return [];
        }

        $products = \App\Models\Product::with('services:id')
            ->whereIn('id', array_keys($quantities))
            ->get();

        $resolved = [];
        foreach ($quantities as $productId => $quantity) {
            $product = $products->firstWhere('id', $productId);

            if (! $product || ! $product->is_active) {
                throw new \Exception('Produk yang dipilih tidak tersedia.', 422);
            }

            $matchesService = ($product->service_scope ?? 'all') === 'all'
                || $product->services->contains('id', $serviceId);

            if (! $matchesService) {
                throw new \Exception('Produk "'.$product->name.'" tidak tersedia untuk layanan ini.', 422);
            }

            $unitPrice = (int) $product->price;
            $resolved[] = [
                'product_id' => (int) $product->id,
                'product_name' => (string) $product->name,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $unitPrice * $quantity,
            ];
        }

        return $resolved;
    }
}
