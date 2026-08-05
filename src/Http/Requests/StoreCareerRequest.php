<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage careers') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'job_title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(array_column(EmploymentType::cases(), 'value'))],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_year' => ['required', 'integer', 'digits:4'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'is_current' => ['boolean'],
            'end_year' => ['nullable', 'integer', 'digits:4'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
