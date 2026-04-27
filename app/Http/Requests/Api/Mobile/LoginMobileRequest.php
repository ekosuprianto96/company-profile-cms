<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class LoginMobileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = trim((string) $this->login);

        if (! filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $login = $this->normalizePhone($login);
        } else {
            $login = strtolower($login);
        }

        $this->merge([
            'login' => $login,
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string|max:150',
            'password' => 'required|string|min:6|max:150',
            'device_name' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Email atau nomor telepon wajib diisi.',
            'password.required' => 'Password tidak boleh kosong.',
            'device_name.required' => 'Nama device wajib diisi.',
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
