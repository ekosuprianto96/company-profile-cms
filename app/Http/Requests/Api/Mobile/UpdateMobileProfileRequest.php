<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMobileProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email !== null && $this->email !== '' ? strtolower(trim((string) $this->email)) : null,
            'phone' => $this->normalizePhone($this->phone),
            'name' => trim((string) $this->name),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'required|string|min:3|max:150',
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('mobile_users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                'string',
                'min:10',
                'max:25',
                Rule::unique('mobile_users', 'phone')->ignore($userId),
            ],
            'avatar' => 'nullable|image|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.min' => 'Nomor telepon terlalu pendek.',
            'phone.max' => 'Nomor telepon terlalu panjang.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.max' => 'Ukuran foto profil maksimal 4 MB.',
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
