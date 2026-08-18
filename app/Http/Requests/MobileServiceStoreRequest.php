<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileServiceStoreRequest extends FormRequest
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
            'form_id' => 'nullable|integer|exists:forms,id',
            'step_template_id' => 'nullable|integer|exists:step_templates,id',
            'price_items' => 'nullable|array',
            'price_items.*.type' => 'nullable|string|max:30',
            'price_items.*.label' => 'nullable|string|max:150',
            'price_items.*.amount' => 'nullable|integer|min:0',
            'price_items.*.is_required' => 'nullable|boolean',
            'request_flow_type' => 'required|in:standard,event_project',
            'summary' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon_type' => 'required|in:icon,image',
            'icon' => 'nullable|string|max:80|required_if:icon_type,icon',
            'icon_image' => 'nullable|string|max:255|required_if:icon_type,image',
            'cover_image' => 'nullable|string|max:255',
            'card_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'text_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'badge_text' => 'nullable|string|max:50',
            'price_label' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'projects_label' => 'nullable|string|max:100',
            'estimated_duration' => 'nullable|string|max:100',
            'cta_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_new' => 'required|boolean',
            'is_featured' => 'required|boolean',
            'is_popular' => 'required|boolean',
            'is_active' => 'required|boolean',
            'is_coming_soon' => 'nullable|boolean',
        ];
    }
}
