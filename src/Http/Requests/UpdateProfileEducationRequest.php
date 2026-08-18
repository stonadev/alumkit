<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'level' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:2099'],
            'start_month' => ['nullable', 'integer', 'between:1,12'],
            'end_year' => ['nullable', 'integer', 'digits:4', 'gte:start_year', 'min:1900', 'max:2099'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
