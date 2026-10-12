<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndicatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:1000'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => $text,
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'feedback' => ['nullable', 'array'],
            'feedback.*.feedback_text' => $text,
            'feedback.*.recommendation_text' => $text,
        ];
    }
}
