<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'display_name' => 'required|min:3|max:50|string',
            'name' => 'min:6|max:255|string'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'display_name.required' => 'Display name tidak boleh kosong.',
            'display_name.min' => 'Display name minimal 3 karakter.',
            'display_name.max' => 'Display name maksimal 50 karakter.',
            'display_name.string' => 'Display name harus berupa string yang valid.',
            'name.min' => 'Name minimal 6 karakter.',
            'name.max' => 'Name maksimal 255 karakter.',
            'name.string' => 'Name harus berupa string yang valid.'
        ];
    }
}
