<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
        $rules = [
            'nama' => 'required|min:3|max:50',
            'url' => 'required|min:3|max:255',
            'deskripsi' => 'max:255'
        ];
        // Menambahkan kondisi untuk 'icon'
        if (empty($this->request->get('id_module'))) {
            $rules['icon'] = 'required|min:3|max:25';
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama tidak boleh kosong.',
            'nama.min' => 'Nama module minimal 3 karakter.',
            'nama.max' => 'Nama module maksimal 50 karakter.',
            'url.required' => 'URL tidak boleh kosong.',
            'url.min' => 'URL minimal 3 karakter.',
            'url.max' => 'URL maksimal 255 karakter.',
            'icon.required' => 'Icon tidak boleh kosong jika module tidak dipilih.'
        ];
    }
}
