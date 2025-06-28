<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LayananUpdateRequest extends FormRequest
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
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'icon' => 'required|string|max:50'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul harus diisi.',
            'title.max' => 'Judul tidak boleh lebih dari 150 karakter.',
            'title.string' => 'Judul harus berupa string yang valid.',
            'content.required' => 'Content harus diisi.',
            'content.string' => 'Content harus berupa string yang valid.',
            'icon.required' => 'Icon harus diisi.',
            'icon.string' => 'Icon harus berupa string yang valid.',
            'icon.max' => 'Icon tidak boleh lebih dari 50 karakter.'
        ];
    }
}
