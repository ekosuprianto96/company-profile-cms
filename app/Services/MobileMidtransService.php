<?php

namespace App\Services;

use App\Models\MobileServiceRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class MobileMidtransService
{
    public function createSnapTransaction(MobileServiceRequest $serviceRequest, string $paymentMethod): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new \Exception('MIDTRANS_SERVER_KEY belum diatur.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $this->orderId($serviceRequest),
                'gross_amount' => (int) $serviceRequest->total_amount,
            ],
            'customer_details' => [
                'first_name' => $serviceRequest->user?->name ?? 'Pengguna',
                'email' => $serviceRequest->user?->email,
                'phone' => $serviceRequest->user?->phone,
            ],
            'item_details' => array_values(array_filter([
                [
                    'id' => 'survey-fee',
                    'price' => (int) $serviceRequest->survey_fee,
                    'quantity' => 1,
                    'name' => 'Biaya Survey',
                ],
                ...$serviceRequest->products->map(fn ($product) => [
                    'id' => 'product-'.$product->id,
                    'price' => (int) $product->unit_price,
                    'quantity' => (int) $product->quantity,
                    'name' => \Illuminate\Support\Str::limit((string) $product->product_name, 45, ''),
                ])->all(),
                ((int) $serviceRequest->discount_amount > 0 ? [
                    'id' => 'voucher-discount',
                    'price' => -1 * (int) $serviceRequest->discount_amount,
                    'quantity' => 1,
                    'name' => 'Diskon Voucher',
                ] : null),
                [
                    'id' => 'tax-fee',
                    'price' => (int) $serviceRequest->tax_amount,
                    'quantity' => 1,
                    'name' => 'Pajak',
                ],
            ])),
            'callbacks' => [
                'finish' => config('services.midtrans.finish_url'),
            ],
            'notification_url' => config('services.midtrans.callback_url'),
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24,
            ],
            'enabled_payments' => $this->enabledPayments($paymentMethod),
        ];

        $responseData = $this->requestSnap($payload);

        return [
            'order_id' => $payload['transaction_details']['order_id'],
            'gross_amount' => (int) $serviceRequest->total_amount,
            'token' => $responseData['token'] ?? null,
            'redirect_url' => $responseData['redirect_url'] ?? null,
            'finish_url' => config('services.midtrans.finish_url'),
            'raw' => $responseData,
        ];
    }

    /** Buat transaksi Snap untuk order produk (item + ongkir − diskon). */
    public function createProductOrderSnapTransaction(\App\Models\ProductOrder $order, string $paymentMethod): array
    {
        if ((string) config('services.midtrans.server_key') === '') {
            throw new \Exception('MIDTRANS_SERVER_KEY belum diatur.');
        }

        $order->loadMissing('items', 'user');

        $itemDetails = array_values(array_filter([
            ...$order->items->map(fn ($item) => [
                'id' => 'item-' . $item->id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => \Illuminate\Support\Str::limit((string) $item->product_name, 45, ''),
            ])->all(),
            (int) $order->shipping_fee > 0 ? [
                'id' => 'shipping-fee',
                'price' => (int) $order->shipping_fee,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ] : null,
            (int) $order->discount_amount > 0 ? [
                'id' => 'voucher-discount',
                'price' => -1 * (int) $order->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon Voucher',
            ] : null,
            // Biaya survey/jasa bila order digabung dengan layanan (grand_total > bagian produk).
            (function () use ($order) {
                $productPart = (int) $order->subtotal - (int) $order->discount_amount + (int) $order->shipping_fee;
                $surveyCharge = (int) $order->grand_total - $productPart;

                return $surveyCharge > 0 ? [
                    'id' => 'service-survey',
                    'price' => $surveyCharge,
                    'quantity' => 1,
                    'name' => 'Biaya Survey Jasa',
                ] : null;
            })(),
        ]));

        $orderId = $order->order_number . '-' . now()->format('YmdHis');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name ?: ($order->user?->name ?? 'Pengguna'),
                'email' => $order->customer_email ?: $order->user?->email,
                'phone' => $order->customer_phone ?: $order->user?->phone,
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => config('services.midtrans.finish_url'),
            ],
            'notification_url' => config('services.midtrans.callback_url'),
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24,
            ],
            'enabled_payments' => $this->enabledPayments($paymentMethod),
        ];

        $responseData = $this->requestSnap($payload);

        return [
            'order_id' => $orderId,
            'gross_amount' => (int) $order->grand_total,
            'token' => $responseData['token'] ?? null,
            'redirect_url' => $responseData['redirect_url'] ?? null,
            'finish_url' => config('services.midtrans.finish_url'),
            'raw' => $responseData,
        ];
    }

    /** Kirim payload ke Snap API; lempar 503 jika gateway tak terjangkau. */
    protected function requestSnap(array $payload): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->connectTimeout(5)
                ->timeout(10)
                ->acceptJson()
                ->post('https://app.'.($this->isProduction() ? 'midtrans.com' : 'sandbox.midtrans.com').'/snap/v1/transactions', $payload);
        } catch (ConnectionException $exception) {
            // Server tidak bisa menjangkau Midtrans (timeout / jaringan). Beri kode 503
            // agar aplikasi bisa menawarkan retry atau Transfer Manual, bukan menampilkan pesan cURL mentah.
            throw new \Exception(
                'Layanan pembayaran otomatis sedang tidak dapat dihubungi. Silakan coba lagi atau gunakan Transfer Manual.',
                503,
                $exception
            );
        }

        if ($response->failed()) {
            throw new \Exception($response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.');
        }

        return $response->json();
    }

    public function handleNotification(array $payload): array
    {
        $this->assertValidSignature($payload);

        return $payload;
    }

    public function transactionStatus(string $orderId): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new \Exception('MIDTRANS_SERVER_KEY belum diatur.');
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->connectTimeout(5)
            ->timeout(10)
            ->acceptJson()
            ->get($this->statusUrl($orderId));

        if ($response->failed()) {
            throw new \Exception($response->json('error_messages.0') ?? 'Gagal memuat status transaksi Midtrans.');
        }

        return $response->json();
    }

    public function orderId(MobileServiceRequest $serviceRequest): string
    {
        return $serviceRequest->transaction_code_label . '-' . now()->format('YmdHis');
    }

    public function isGatewayMethod(string $paymentMethod): bool
    {
        return $paymentMethod !== 'manual_transfer';
    }

    protected function enabledPayments(string $paymentMethod): array
    {
        return match ($paymentMethod) {
            'qris' => ['qris'],
            'va_bca' => ['bca_va'],
            'va_bni' => ['bni_va'],
            'va_mandiri' => ['mandiri_va'],
            'gopay' => ['gopay'],
            'dana' => ['dana'],
            'ovo' => ['ovo'],
            default => [],
        };
    }

    protected function isProduction(): bool
    {
        return filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);
    }

    protected function statusUrl(string $orderId): string
    {
        $baseUrl = $this->isProduction() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        return $baseUrl . '/v2/' . $orderId . '/status';
    }

    protected function assertValidSignature(array $payload): void
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            throw new \Exception('Payload notifikasi Midtrans tidak lengkap.');
        }

        $calculated = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($calculated, $signatureKey)) {
            throw new \Exception('Signature notifikasi Midtrans tidak valid.');
        }
    }
}
