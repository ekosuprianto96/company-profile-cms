<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TwilioVerifyService
{
    public function sendVerification(string $phone): array
    {
        $response = Http::asForm()
            ->withBasicAuth($this->accountSid(), $this->authToken())
            ->post($this->serviceUrl('/Verifications'), [
                'To' => $phone,
                'Channel' => 'sms',
            ]);

        if ($response->failed()) {
            throw new \Exception('Gagal mengirim OTP SMS melalui Twilio.');
        }

        return $response->json();
    }

    public function checkVerification(string $phone, string $code): array
    {
        $response = Http::asForm()
            ->withBasicAuth($this->accountSid(), $this->authToken())
            ->post($this->serviceUrl('/VerificationCheck'), [
                'To' => $phone,
                'Code' => $code,
            ]);

        if ($response->failed()) {
            throw new \Exception('Gagal memverifikasi OTP SMS melalui Twilio.');
        }

        return $response->json();
    }

    private function serviceUrl(string $path): string
    {
        return sprintf(
            'https://verify.twilio.com/v2/Services/%s%s',
            config('services.twilio.verify_service_sid'),
            $path
        );
    }

    private function accountSid(): string
    {
        $sid = (string) config('services.twilio.account_sid');

        if ($sid === '') {
            throw new \Exception('TWILIO_ACCOUNT_SID belum diatur.');
        }

        return $sid;
    }

    private function authToken(): string
    {
        $token = (string) config('services.twilio.auth_token');

        if ($token === '') {
            throw new \Exception('TWILIO_AUTH_TOKEN belum diatur.');
        }

        if ((string) config('services.twilio.verify_service_sid') === '') {
            throw new \Exception('TWILIO_VERIFY_SERVICE_SID belum diatur.');
        }

        return $token;
    }
}
