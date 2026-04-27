<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspirePostStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'summary' => 'nullable|string|max:300',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'reading_time' => 'required|integer|min:1|max:60',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul tidak boleh lebih dari 200 karakter.',
            'category.required' => 'Kategori wajib diisi.',
            'summary.max' => 'Summary tidak boleh lebih dari 300 karakter.',
            'content.required' => 'Konten wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Format thumbnail harus jpg, jpeg, png, svg, atau webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'accent_color.regex' => 'Warna aksen harus berformat hex, misalnya #275a56.',
            'reading_time.required' => 'Estimasi baca wajib diisi.',
            'reading_time.integer' => 'Estimasi baca harus berupa angka.',
        ];
    }
}
