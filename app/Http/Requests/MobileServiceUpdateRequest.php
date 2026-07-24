<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'category_id' => 'nullable|integer|exists:categories,id',
            'request_flow_type' => 'required|in:standard,event_project',
            'summary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon_type' => 'required|in:icon,image',
            'icon' => 'nullable|string|max:80|required_if:icon_type,icon',
            'icon_image' => 'nullable|string|max:255',
            'icon_image_path' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|max:255',
            'cover_image_path' => 'nullable|string|max:255',
            'card_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'text_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'badge_text' => 'nullable|string|max:50',
            'price_label' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'projects_label' => 'nullable|string|max:100',
            'estimated_duration' => 'nullable|string|max:100',
            'cta_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'need_types' => 'nullable|array',
            'need_types.*' => 'integer|exists:mobile_service_need_types,id',
            'is_new' => 'required|boolean',
            'is_featured' => 'required|boolean',
            'is_popular' => 'required|boolean',
            'is_active' => 'required|boolean',
            'is_coming_soon' => 'nullable|boolean',
        ];
    }
}
