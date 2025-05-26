<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:150'
        ];
    }

    public function messages() :array
    {
        return [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.max' => 'Panjang email maximal 255 karakter.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Panjang katasandi minimal 6 karakter.',
            'password.max' => 'Panjang katasandi maximal 150 karakter.'
        ];
    }
}
