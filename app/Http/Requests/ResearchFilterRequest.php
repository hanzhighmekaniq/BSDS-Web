<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResearchFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'case_id' => ['nullable', 'integer', 'exists:cases,id'],
            'mode' => ['nullable', 'in:practice,assessment'],
            'status' => ['nullable', 'in:in_progress,completed,abandoned'],
        ];
    }
}
