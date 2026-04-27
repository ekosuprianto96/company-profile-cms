<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileServiceNeedTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'required|boolean',
        ];
    }
}

