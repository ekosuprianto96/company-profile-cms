<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class SendMobileOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'phone' => $this->normalizePhone($this->phone),
        ]);
    }

    public function rules(): array
    {
        return [
            'purpose' => 'required|in:register,login',
            'channel' => 'required|in:email,sms',
            'email' => 'nullable|email|max:150|required_without:phone',
            'phone' => 'nullable|string|min:10|max:25|required_without:email',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->purpose === 'register' && $this->channel === 'email' && empty($this->email)) {
                $validator->errors()->add('email', 'Email wajib diisi untuk pengiriman OTP email.');
            }

            if ($this->purpose === 'register' && $this->channel === 'sms' && empty($this->phone)) {
                $validator->errors()->add('phone', 'Nomor telepon wajib diisi untuk pengiriman OTP SMS.');
            }

            if ($this->purpose === 'login' && empty($this->email) && empty($this->phone)) {
                $validator->errors()->add('phone', 'Isi nomor telepon atau email untuk kirim OTP login.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'purpose.required' => 'Tujuan OTP wajib diisi.',
            'purpose.in' => 'Tujuan OTP tidak valid.',
            'channel.required' => 'Channel OTP wajib diisi.',
            'channel.in' => 'Channel OTP tidak valid.',
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return '+' . ltrim((string) $digits, '+');
    }
}
