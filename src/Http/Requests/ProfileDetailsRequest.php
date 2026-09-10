<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Enums\BloodGroup;
use Alumkit\Alumkit\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileDetailsRequest extends FormRequest
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
        $rules = [
            'photo' => ['nullable', 'image', 'max:2048'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(array_column(Gender::cases(), 'value'))],
            'blood_group' => ['nullable', Rule::in(array_column(BloodGroup::cases(), 'value'))],
            'present_address' => ['nullable', 'string', 'max:255'],
            'permanent_address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'emergency_contact' => ['nullable', 'array'],
            'emergency_contact.name' => ['nullable', 'string', 'max:255'],
            'emergency_contact.phone' => ['nullable', 'string', 'max:255'],
            'emergency_contact.relation' => ['nullable', 'string', 'max:255'],
        ];

        $localNames = config('alumkit.local_names', []);

        if ($localNames) {
            $rules['local_names'] = ['nullable', 'array'];

            foreach ($localNames as $code => $langConfig) {
                $required = ($langConfig['required'] ?? false) ? 'required' : 'nullable';
                $rules["local_names.{$code}"] = [$required, 'string', 'max:255', 'regex:/^[\p{Bengali}\s]+$/u'];
            }
        }

        return $rules;
    }
}
