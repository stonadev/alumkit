<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Actions\Fortify\PasswordValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => __('alumkit::validation.name_latin_only'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')],
            'password' => [...$this->passwordRules(), 'confirmed'],
            'educations' => ['required', 'array', 'min:1'],
            'educations.*.level' => ['required', 'string', 'max:255'],
            'educations.*.institution' => ['required', 'string', 'max:255'],
            'educations.*.student_id' => ['nullable', 'string', 'max:255'],
            'educations.*.subject' => ['required', 'string', 'max:255'],
            'educations.*.start_year' => ['required', 'integer', 'digits:4'],
            'educations.*.start_month' => ['nullable', 'integer', 'between:1,12'],
            'educations.*.is_current' => ['sometimes', 'boolean'],
            'educations.*.end_year' => ['nullable', 'integer', 'digits:4', 'gte:educations.*.start_year'],
            'educations.*.end_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
