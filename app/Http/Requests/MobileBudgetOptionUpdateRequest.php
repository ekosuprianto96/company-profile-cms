<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileBudgetOptionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'min_amount' => 'nullable|integer|min:0|max:999999999999',
            'max_amount' => 'nullable|integer|min:0|max:999999999999',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'required|boolean',
        ];
    }
}

