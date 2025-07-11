<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KategoriStoreRequest extends FormRequest
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
            'name' => 'required|string|max:150',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Kategori harus diisi.',
            'name.string' => 'Kategori harus berupa string yang valid.',
            'name.max' => 'Kategori tidak boleh lebih dari 150 karakter.'
        ];
    }
}
