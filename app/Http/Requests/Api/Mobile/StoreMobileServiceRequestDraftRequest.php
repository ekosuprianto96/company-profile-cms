<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServiceRequestDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile_service_id' => 'required|exists:mobile_services,id',
            'mobile_service_need_type_id' => 'nullable|exists:mobile_service_need_types,id',
            'mobile_budget_option_id' => 'nullable|exists:mobile_budget_options,id',
            'building_key' => 'required|string|max:50',
            'building_label' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'issue_photos' => 'nullable|array',
            'issue_photos.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'survey_address' => 'required|string|max:5000',
            'survey_region' => 'nullable|string|max:5000',
            'survey_latitude' => 'required|numeric',
            'survey_longitude' => 'required|numeric',
            'survey_date' => 'required|date',
            'survey_fee' => 'nullable|integer|min:0',
            'tax_percentage' => 'nullable|integer|min:0|max:100',
            'tax_amount' => 'nullable|integer|min:0',
            'total_amount' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_service_id.required' => 'Layanan wajib dipilih.',
            'building_key.required' => 'Jenis bangunan wajib dipilih.',
            'building_label.required' => 'Label jenis bangunan wajib diisi.',
            'issue_photos.*.file' => 'Foto masalah harus berupa file gambar.',
            'survey_address.required' => 'Alamat survey wajib diisi.',
            'survey_date.required' => 'Tanggal survey wajib diisi.',
        ];
    }
}
