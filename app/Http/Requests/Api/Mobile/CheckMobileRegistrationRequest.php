<?php

namespace App\Http\Requests\Api\Mobile;

/**
 * Validasi awal registrasi (dipanggil SEBELUM memilih channel OTP): memastikan
 * email/telepon unik dan password memenuhi aturan, TANPA membuat akun. Mewarisi
 * aturan RegisterMobileRequest kecuali otp_channel yang belum dipilih di tahap ini.
 */
class CheckMobileRegistrationRequest extends RegisterMobileRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['otp_channel']);

        return $rules;
    }

    public function withValidator($validator): void
    {
        // Belum ada otp_channel di tahap pengecekan — lewati validasi khusus channel.
    }
}
