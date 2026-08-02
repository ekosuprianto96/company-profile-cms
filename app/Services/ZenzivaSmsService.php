<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengiriman SMS via Zenziva (SMS Reguler/Masking). Teks pesan berasal dari
 * template notifikasi (channel `sms`), bukan hardcoded.
 */
class ZenzivaSmsService
{
    /** Kirim SMS. Mengembalikan respons JSON/array Zenziva. */
    public function send(string $phone, string $message): array
    {
        $userkey = (string) config('services.zenziva.userkey');
        $passkey = (string) config('services.zenziva.passkey');
        $baseUrl = rtrim((string) config('services.zenziva.base_url'), '/');

        if ($userkey === '' || $passkey === '' || $baseUrl === '') {
            throw new \Exception('Kredensial Zenziva (userkey/passkey/base_url) belum diatur.', 500);
        }

        $response = Http::asForm()->post($baseUrl, [
            'userkey' => $userkey,
            'passkey' => $passkey,
            'to' => $this->normalizePhone($phone),
            'msg' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Zenziva SMS gagal: ' . $response->body());
            throw new \Exception('Gagal mengirim OTP SMS melalui Zenziva.', 502);
        }

        $data = $response->json() ?? ['raw' => $response->body()];

        // Zenziva mengembalikan status di dalam node "status"/"messages"; anggap
        // gagal bila statusnya menandakan error.
        $status = (string) data_get($data, 'messages.status', data_get($data, 'status', '0'));
        if (in_array(strtolower($status), ['1', 'error', 'failed'], true)) {
            Log::error('Zenziva SMS ditolak: ' . json_encode($data));
            throw new \Exception('OTP SMS ditolak oleh Zenziva.', 502);
        }

        return is_array($data) ? $data : ['raw' => $data];
    }

    /** Normalisasi ke format 628xxxx (umum diterima Zenziva). */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone) ?? $phone;
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }
        if (str_starts_with($phone, '62')) {
            return $phone;
        }
        return $phone;
    }
}
