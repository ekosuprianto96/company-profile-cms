<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMobileRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:150',
            'email' => 'nullable|email|max:150|required_without:phone|unique:mobile_users,email',
            'phone' => 'nullable|string|min:10|max:25|required_without:email|unique:mobile_users,phone',
            // Password kuat: min 8 + wajib mengandung huruf, angka, dan simbol.
            'password' => ['required', 'string', 'min:8', 'max:150', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'],
            'otp_channel' => 'required|in:email,sms',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->otp_channel === 'email' && empty($this->email)) {
                $validator->errors()->add('email', 'Email wajib diisi jika OTP dikirim lewat email.');
            }

            if ($this->otp_channel === 'sms' && empty($this->phone)) {
                $validator->errors()->add('phone', 'Nomor telepon wajib diisi jika OTP dikirim lewat SMS.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong.',
            'email.required_without' => 'Isi email atau nomor telepon.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required_without' => 'Isi nomor telepon atau email.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf, angka, dan simbol.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'otp_channel.required' => 'Channel OTP wajib dipilih.',
            'otp_channel.in' => 'Channel OTP tidak valid.',
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
