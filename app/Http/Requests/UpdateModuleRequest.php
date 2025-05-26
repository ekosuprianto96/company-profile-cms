<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
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
            'nama' => 'required|min:3|max:50',
            'id_group' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'nama tidak boleh kosong.',
            'nama.min' => 'nama module minimal 3 karakter.',
            'nama.max' => 'nama module maximal 50 karakter.'
        ];
    }
}
