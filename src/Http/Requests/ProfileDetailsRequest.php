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
        $user = $this->user();
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/u'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,'.($user->id ?? 'NULL')],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'unique:users,phone,'.($user->id ?? 'NULL')],
            'photo' => ['nullable', 'image', 'max:2048'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(array_column(Gender::cases(), 'value'))],
            'blood_group' => ['nullable', Rule::in(array_column(BloodGroup::cases(), 'value'))],
            'present_address' => ['required', 'string', 'max:255'],
            'permanent_address' => ['required', 'string', 'max:255'],
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
                $fieldRules = [$required, 'string', 'max:255'];
                $pattern = self::scriptPattern($code);

                if ($pattern) {
                    $fieldRules[] = 'regex:'.$pattern;
                }
                $rules["local_names.{$code}"] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Map language codes to Unicode script validation patterns.
     * Only codes listed here get script-level validation; unknown codes
     * fall through to generic string validation.
     */
    private static function scriptPattern(string $code): ?string
    {
        return match ($code) {
            'bn' => '/^[\p{Bengali}\p{Cf}\s]+$/u',
            default => null,
        };
    }
}
