<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_start' => $this->boolean('is_start'),
            'is_ending' => $this->boolean('is_ending'),
        ]);
    }

    public function rules(): array
    {
        return [
            'step_code' => ['required', 'string', 'max:30'],
            'scenario_type' => ['required', Rule::in(['main', 'branch', 'remedial', 'ending'])],
            'is_start' => ['boolean'],
            'is_ending' => ['boolean'],
            'decision_no' => ['nullable', 'integer', 'min:1', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'story_text' => ['required', 'string', 'max:3000'],
            'customer_dialogue' => ['nullable', 'string', 'max:1000'],
            'new_problem_text' => ['nullable', 'string', 'max:1000'],
            'max_investigations' => ['required', 'integer', 'min:0', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'options' => ['array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['nullable', 'string', 'max:5'],
            'options.*.option_text' => ['nullable', 'string', 'max:1000'],
            'options.*.consequence_text' => ['nullable', 'string', 'max:2000'],
            'options.*.consequence_type' => ['nullable', Rule::in(['positive', 'neutral', 'negative'])],
            'options.*.outcome_tag' => ['nullable', 'string', 'max:100'],
            'options.*.next_scenario_id' => ['nullable', 'integer', 'exists:scenarios,id'],
            'options.*.sort_order' => ['nullable', 'integer'],
            'options.*.scores' => ['array'],
            'options.*.scores.*' => ['nullable', Rule::in(['0', '1', '2'])],

            'investigation_items' => ['array'],
            'investigation_items.*.id' => ['nullable', 'integer'],
            'investigation_items.*.title' => ['nullable', 'string', 'max:255'],
            'investigation_items.*.type' => ['nullable', 'string', 'max:50'],
            'investigation_items.*.content' => ['nullable', 'string', 'max:2000'],
            'investigation_items.*.is_relevant' => ['boolean'],
            'investigation_items.*.sort_order' => ['nullable', 'integer'],

            'hints' => ['array', 'max:5'],
            'hints.*.id' => ['nullable', 'integer'],
            'hints.*.level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'hints.*.hint_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Validasi lintas-field yang butuh konteks route (case_id saat ini, id skenario saat update).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $case = $this->route('case');
            $scenario = $this->route('scenario'); // null saat create

            // step_code harus unik di dalam kasus ini.
            $exists = $case->scenarios()
                ->where('step_code', $this->input('step_code'))
                ->when($scenario, fn ($q) => $q->where('id', '!=', $scenario->id))
                ->exists();

            if ($exists) {
                $v->errors()->add('step_code', 'Step code ini sudah dipakai skenario lain di kasus yang sama.');
            }

            // next_scenario_id (jika diisi) harus milik kasus yang sama, bukan kasus lain.
            foreach ($this->input('options', []) as $i => $option) {
                $nextId = $option['next_scenario_id'] ?? null;
                if ($nextId && ! $case->scenarios()->where('id', $nextId)->exists()) {
                    $v->errors()->add("options.$i.next_scenario_id", 'Skenario tujuan harus berasal dari kasus yang sama.');
                }
            }
        });
    }
}
