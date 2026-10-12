<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim(strip_tags($this->name)) : $this->name,
            'nis' => is_string($this->nis) ? trim($this->nis) : $this->nis,
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(['student', 'researcher', 'admin'])],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::min(8)],
            'nis' => ['nullable', 'required_if:role,student', 'string', 'max:50', Rule::unique('student_profiles', 'nis')->ignore($user?->studentProfile?->id)],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'absence_no' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}
