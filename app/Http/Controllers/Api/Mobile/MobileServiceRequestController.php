<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Requests\Api\Mobile\StoreMobileServiceRequestDraftRequest;
use App\Http\Requests\Api\Mobile\UpdateMobileServiceRequestPaymentMethodRequest;
use App\Services\MobileServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MobileServiceRequestController extends ApiController
{
    public function __construct(
        protected MobileServiceRequestService $mobileServiceRequestService
    ) {}

    public function meta()
    {
        try {
            return $this->success([
                'meta' => $this->mobileServiceRequestService->meta(),
            ], 'Meta pengajuan berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Mobile service request meta error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error('Gagal memuat meta pengajuan.', 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $serviceRequests = $this->mobileServiceRequestService->listForUser($request->user());

            return $this->success([
                'orders' => $serviceRequests->map(fn ($serviceRequest) => $this->serviceRequestPayload($serviceRequest))->values(),
            ], 'Daftar pengajuan berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Mobile service request list error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error('Gagal memuat daftar pengajuan.', $status);
        }
    }

    public function show(Request $request, int $id)
    {
        try {
            $serviceRequest = $this->mobileServiceRequestService->findForUser($request->user(), $id);

            if (! $serviceRequest) {
                return $this->error('Data pengajuan tidak ditemukan.', 404);
            }

            return $this->success([
                'order' => $this->serviceRequestPayload($serviceRequest),
            ], 'Detail pengajuan berhasil dimuat.');
        } catch (\Throwable $th) {
            Log::error('Mobile service request detail error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error('Gagal memuat detail pengajuan.', $status);
        }
    }

    public function uploadPaymentProof(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);

            $serviceRequest = $this->mobileServiceRequestService->uploadPaymentProof(
                $request->user(),
                $id,
                $validated['proof'],
            );

            return $this->success([
                'service_request' => $this->serviceRequestPayload($serviceRequest),
            ], 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
        } catch (\Throwable $th) {
            Log::error('Upload mobile payment proof error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error($th->getMessage(), $status);
        }
    }

    public function uploadIssuePhoto(Request $request)
    {
        try {
            $validated = $request->validate([
                'photo' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            $file = $validated['photo'];
            $extension = $file->getClientOriginalExtension();
            $fileName = now()->format('Y-m-d') . '-' . Str::uuid() . '.' . $extension;
            $relativePath = $file->storeAs('mobile/service-requests', $fileName, 'public');

            return $this->success([
                'file_name' => $fileName,
                'path' => $relativePath,
                'url' => storageUrl($relativePath),
                'mime_type' => $file->getMimeType(),
            ], 'Foto berhasil diupload.');
        } catch (\Throwable $th) {
            Log::error('Upload mobile issue photo error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error($th->getMessage(), $status);
        }
    }

    public function storeDraft(StoreMobileServiceRequestDraftRequest $request)
    {
        try {
            $serviceRequest = $this->mobileServiceRequestService->createDraft($request->user(), $request->validated());

            return $this->success([
                'service_request' => $this->serviceRequestPayload($serviceRequest),
            ], 'Draft pengajuan berhasil disimpan.', 201);
        } catch (\Throwable $th) {
            Log::error('Store mobile service request draft error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error($th->getMessage(), $status);
        }
    }

    public function updatePaymentMethod(UpdateMobileServiceRequestPaymentMethodRequest $request, int $id)
    {
        try {
            $serviceRequest = $this->mobileServiceRequestService->selectPaymentMethod(
                $request->user(),
                $id,
                $request->validated('payment_method'),
                $request->validated('voucher_id'),
            );

            return $this->success([
                'service_request' => $this->serviceRequestPayload($serviceRequest),
            ], 'Metode pembayaran berhasil dipilih.');
        } catch (\Throwable $th) {
            Log::error('Update mobile service request payment method error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error($th->getMessage(), $status);
        }
    }

    public function cancel(Request $request, int $id)
    {
        try {
            $serviceRequest = $this->mobileServiceRequestService->cancel($request->user(), $id);

            return $this->success([
                'service_request' => $this->serviceRequestPayload($serviceRequest),
            ], 'Pengajuan berhasil dibatalkan.');
        } catch (\Throwable $th) {
            Log::error('Cancel mobile service request error: ' . $th->getMessage());

            $status = (int) $th->getCode();
            if ($status <= 0) {
                $status = 500;
            }

            return $this->error($th->getMessage(), $status);
        }
    }

    protected function serviceRequestPayload($serviceRequest): array
    {
        return [
            'id' => $serviceRequest->id,
            'transaction_code' => $serviceRequest->transaction_code_label,
            'transaction_code_label' => $serviceRequest->transaction_code_label,
            'status' => $serviceRequest->status,
            'can_cancel' => $serviceRequest->canBeCancelled(),
            'payment_status' => $serviceRequest->payment_status,
            'payment_method' => $serviceRequest->payment_method,
            'payment_gateway_provider' => $serviceRequest->payment_gateway_provider,
            'payment_proof_url' => storageUrl($serviceRequest->payment_proof_path),
            'payment_proof_uploaded_at' => optional($serviceRequest->payment_proof_uploaded_at)?->toISOString(),
            'survey_fee' => (int) $serviceRequest->survey_fee,
            'tax_percentage' => (int) $serviceRequest->tax_percentage,
            'tax_amount' => (int) $serviceRequest->tax_amount,
            'discount_amount' => (int) $serviceRequest->discount_amount,
            'voucher_id' => $serviceRequest->voucher_id,
            'products_amount' => (int) $serviceRequest->products_amount,
            'products' => $serviceRequest->products->map(fn ($product) => [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->product_name,
                'unit_price' => (int) $product->unit_price,
                'quantity' => (int) $product->quantity,
                'subtotal' => (int) $product->subtotal,
                'image' => storageUrl($product->product?->primary_image),
            ])->all(),
            'total_amount' => (int) $serviceRequest->total_amount,
            'drafted_at' => optional($serviceRequest->drafted_at)?->toISOString(),
            'submitted_at' => optional($serviceRequest->submitted_at)?->toISOString(),
            'payment_method_selected_at' => optional($serviceRequest->payment_method_selected_at)?->toISOString(),
            'reviewed_at' => optional($serviceRequest->reviewed_at)?->toISOString(),
            'approved_at' => optional($serviceRequest->approved_at)?->toISOString(),
            'rejected_at' => optional($serviceRequest->rejected_at)?->toISOString(),
            'handled_by_user' => $serviceRequest->handledBy ? [
                'id' => $serviceRequest->handledBy->id,
                'name' => $serviceRequest->handledBy->name,
            ] : null,
            'admin_note' => $serviceRequest->admin_note,
            'rejection_reason' => $serviceRequest->rejection_reason,
            'paid_at' => optional($serviceRequest->paid_at)?->toISOString(),
            'survey_date' => optional($serviceRequest->survey_date)?->format('Y-m-d'),
            'survey_address' => $serviceRequest->survey_address,
            'survey_region' => $serviceRequest->survey_region ?? [],
            'survey_latitude' => (float) $serviceRequest->survey_latitude,
            'survey_longitude' => (float) $serviceRequest->survey_longitude,
            'payment_payload' => $serviceRequest->payment_payload ?? [],
            'draft_payload' => $serviceRequest->draft_payload ?? [],
            'payment_data' => $serviceRequest->getAttribute('payment_data') ?? null,
            'request_flow_type' => $serviceRequest->request_flow_type ?? 'standard',
            'building_key' => $serviceRequest->building_key,
            'building_label' => $serviceRequest->building_label,
            'description' => $serviceRequest->description,
            'issue_photos' => $serviceRequest->issue_photos ?? [],
            'service' => $serviceRequest->service ? [
                'id' => $serviceRequest->service->id,
                'title' => $serviceRequest->service->title,
            ] : null,
        ];
    }

}
