<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'educations' => ['required', 'array', 'min:1'],
            'educations.*.level' => ['required', Rule::in(array_keys(config('alumkit.education.levels', [])))],
            'educations.*.institution' => ['required', 'string', 'max:255'],
            'educations.*.subject' => ['nullable', 'string', 'max:255'],
            'educations.*.start_year' => ['nullable', 'integer', 'digits:4'],
            'educations.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'educations.*.end_year' => ['nullable', 'integer', 'digits:4', 'gte:educations.*.start_year'],
            'educations.*.end_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
