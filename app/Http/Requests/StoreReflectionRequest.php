<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReflectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach (['q1_reason', 'q2_hardest_decision', 'q3_unexpected_consequence', 'q4_future_action'] as $field) {
            $value = $this->input($field);
            $clean[$field] = is_string($value) ? trim(strip_tags($value)) : $value;
        }

        $this->merge($clean);
    }

    public function rules(): array
    {
        $rule = ['required', 'string', 'min:5', 'max:2000'];

        return [
            'q1_reason' => $rule,
            'q2_hardest_decision' => $rule,
            'q3_unexpected_consequence' => $rule,
            'q4_future_action' => $rule,
        ];
    }
}
