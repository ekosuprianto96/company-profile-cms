<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogStoreRequest extends FormRequest
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
            'title' => 'required|max:200|string',
            'content' => 'required|string',
            'kategori_id' => 'required|exists:kategori_blog,id',
            'thumbnail' => 'required|image|mimes:jpg,png,jpeg,svg,webp|max:2000'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul tidak boleh lebih dari 200 karakter.',
            'title.string' => 'Judul harus berupa teks.',

            'content.required' => 'Konten wajib diisi.',
            'content.string' => 'Konten harus berupa teks.',

            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',

            'thumbnail.required' => 'Thumbnail wajib diunggah.',
            'thumbnail.image' => 'Thumbnail harus berupa file gambar.',
            'thumbnail.mimes' => 'Format gambar yang diperbolehkan: JPG, PNG, JPEG, SVG, atau WEBP.',
            'thumbnail.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.'
        ];
    }
}
