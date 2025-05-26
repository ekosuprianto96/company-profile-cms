<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
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
            'nama' => 'required|min:3|max:50',
            'deskripsi' => 'min:6|max:255'
        ];
        
        return $rules;
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama tidak boleh kosong.',
            'nama.min' => 'Nama module minimal 3 karakter.',
            'nama.max' => 'Nama module maksimal 50 karakter.',
            'deskripsi.min' => 'Keterangan minimal 6 karakter.',
            'deskripsi.max' => 'Keterangan maksimal 255 karakter.'
        ];
    }
}
