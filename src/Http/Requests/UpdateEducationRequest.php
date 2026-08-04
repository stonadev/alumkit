<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage educations') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'level' => ['required', Rule::in(array_keys(config('alumkit.education.levels', [])))],
            'institution' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'digits:4'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'end_year' => ['nullable', 'integer', 'digits:4', 'gte:start_year'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
