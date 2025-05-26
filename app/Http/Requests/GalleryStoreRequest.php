<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryStoreRequest extends FormRequest
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
            'title' => 'required|string|max:200|min:3',
            'content' => 'required|string|min:3',
            'file_name' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul harus diisi.',
            'content.required' => 'Content harus diisi.',
            'file_name.required' => 'File belum diupload.',
        ];
    }
}
