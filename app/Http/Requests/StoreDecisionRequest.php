<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_id' => ['required', 'integer', 'exists:scenario_options,id'],
            'confidence_level' => ['required', 'string', 'in:very_unsure,unsure,fairly_confident,confident,very_confident'],
        ];
    }
}
