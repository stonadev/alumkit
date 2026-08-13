<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Enums\EducationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEducationRequest extends FormRequest
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
            'profile_id' => ['required', 'exists:profiles,id'],
            'level' => ['required', Rule::in(array_column(EducationLevel::cases(), 'value'))],
            'institution' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'digits:4'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'end_year' => ['nullable', 'integer', 'digits:4', 'gte:start_year'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
