<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServiceRequestDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $products = $this->input('products');

        if (is_string($products)) {
            $decoded = json_decode($products, true);
            $this->merge(['products' => is_array($decoded) ? $decoded : []]);
        }
    }

    public function rules(): array
    {
        return [
            'request_flow_type' => 'nullable|in:standard,event_project',
            'mobile_service_id' => 'required|exists:mobile_services,id',
            'mobile_service_need_type_id' => 'nullable|exists:mobile_service_need_types,id',
            'mobile_budget_option_id' => 'nullable|exists:mobile_budget_options,id',
            'mobile_event_project_type_id' => 'nullable|exists:mobile_event_project_types,id',
            'mobile_event_project_need_id' => 'nullable|exists:mobile_event_project_needs,id',
            'mobile_event_package_id' => 'nullable|exists:mobile_event_packages,id',
            'mobile_event_budget_option_id' => 'nullable|exists:mobile_event_budget_options,id',
            'event_date' => 'nullable|date',
            'building_key' => 'nullable|string|max:50',
            'building_label' => 'nullable|string|max:255',
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
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:999',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $flowType = $this->input('request_flow_type', 'standard');

            if ($flowType === 'event_project') {
                foreach ([
                    'mobile_event_project_type_id' => 'Jenis project event wajib dipilih.',
                    'mobile_event_project_need_id' => 'Kebutuhan project event wajib dipilih.',
                    'mobile_event_package_id' => 'Paket event wajib dipilih.',
                    'mobile_event_budget_option_id' => 'Anggaran event wajib dipilih.',
                    'event_date' => 'Tanggal event wajib dipilih.',
                ] as $field => $message) {
                    if (! $this->filled($field)) {
                        $validator->errors()->add($field, $message);
                    }
                }

                return;
            }

            foreach ([
                'building_key' => 'Jenis bangunan wajib dipilih.',
                'building_label' => 'Label jenis bangunan wajib diisi.',
            ] as $field => $message) {
                if (! $this->filled($field)) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
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
