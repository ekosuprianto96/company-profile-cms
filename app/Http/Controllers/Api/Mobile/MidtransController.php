<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Services\MobileProductOrderCheckoutService;
use App\Services\MobileServiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends ApiController
{
    public function __construct(
        protected MobileServiceRequestService $mobileServiceRequestService,
        protected MobileProductOrderCheckoutService $productOrderCheckoutService,
    ) {}

    public function notification(Request $request)
    {
        try {
            $orderId = (string) $request->input('order_id', '');

            // Order produk memakai prefix "ORD-"; pengajuan survey memakai "SR-".
            if (str_starts_with($orderId, 'ORD-')) {
                $this->productOrderCheckoutService->handleMidtransNotification($request->all());

                return $this->success([], 'Notifikasi Midtrans (produk) berhasil diproses.');
            }

            $serviceRequest = $this->mobileServiceRequestService->handleMidtransNotification($request->all());

            return $this->success([
                'service_request' => $this->serviceRequestPayload($serviceRequest),
            ], 'Notifikasi Midtrans berhasil diproses.');
        } catch (\Throwable $th) {
            Log::error('Midtrans notification error: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return $this->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    protected function serviceRequestPayload($serviceRequest): array
    {
        return [
            'id' => $serviceRequest->id,
            'transaction_code' => $serviceRequest->transaction_code_label,
            'transaction_code_label' => $serviceRequest->transaction_code_label,
            'status' => $serviceRequest->status,
            'payment_status' => $serviceRequest->payment_status,
            'payment_method' => $serviceRequest->payment_method,
            'midtrans_order_id' => $serviceRequest->midtrans_order_id,
            'midtrans_snap_token' => $serviceRequest->midtrans_snap_token,
            'midtrans_redirect_url' => $serviceRequest->midtrans_redirect_url,
            'midtrans_transaction_status' => $serviceRequest->midtrans_transaction_status,
            'midtrans_payment_type' => $serviceRequest->midtrans_payment_type,
            'survey_fee' => (int) $serviceRequest->survey_fee,
            'tax_percentage' => (int) $serviceRequest->tax_percentage,
            'tax_amount' => (int) $serviceRequest->tax_amount,
            'total_amount' => (int) $serviceRequest->total_amount,
            'survey_date' => optional($serviceRequest->survey_date)?->format('Y-m-d'),
            'survey_address' => $serviceRequest->survey_address,
            'survey_latitude' => (float) $serviceRequest->survey_latitude,
            'survey_longitude' => (float) $serviceRequest->survey_longitude,
            'building_key' => $serviceRequest->building_key,
            'building_label' => $serviceRequest->building_label,
            'description' => $serviceRequest->description,
            'issue_photos' => $serviceRequest->issue_photos ?? [],
        ];
    }
}
