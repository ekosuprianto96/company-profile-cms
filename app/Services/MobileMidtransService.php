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
            'item_details' => [
                [
                    'id' => 'survey-fee',
                    'price' => (int) $serviceRequest->survey_fee,
                    'quantity' => 1,
                    'name' => 'Biaya Survey',
                ],
                [
                    'id' => 'tax-fee',
                    'price' => (int) $serviceRequest->tax_amount,
                    'quantity' => 1,
                    'name' => 'Pajak',
                ],
            ],
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

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->connectTimeout(10)
                ->timeout(30)
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

        $responseData = $response->json();

        return [
            'order_id' => $payload['transaction_details']['order_id'],
            'gross_amount' => (int) $serviceRequest->total_amount,
            'token' => $responseData['token'] ?? null,
            'redirect_url' => $responseData['redirect_url'] ?? null,
            'finish_url' => config('services.midtrans.finish_url'),
            'raw' => $responseData,
        ];
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
