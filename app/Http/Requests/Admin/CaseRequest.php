<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && is_string($this->code)) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }

        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'role_title' => ['nullable', 'string', 'max:100'],
            'briefing_text' => ['nullable', 'string', 'max:5000'],
            'level' => ['required', Rule::in(['foundation', 'developing', 'challenge'])],
            'estimated_time' => ['required', 'integer', 'min:1', 'max:240'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules['code'] = ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', 'unique:cases,code'];
        }

        return $rules;
    }
}
