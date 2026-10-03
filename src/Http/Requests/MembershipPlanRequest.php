<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class MembershipPlanRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'term_days' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'term_months' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_lifetime' => ['sometimes', 'boolean'],
            // Computed base (days) from term_days + term_months; declared so it
            // survives into validated() and reaches the model.
            'duration_days' => ['nullable', 'integer'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $days = $this->input('term_days');
            $months = $this->input('term_months');
            $lifetime = (bool) $this->input('is_lifetime', false);

            $set = (int) (filled($days) && (int) $days > 0)
                + (int) (filled($months) && (int) $months > 0)
                + (int) $lifetime;

            if ($set !== 1) {
                $validator->errors()->add('term_days', __('alumkit::membership.term_exclusivity'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Normalize the authored term into a single base value stored in days:
        // a day term is kept as-is, a month term becomes months * 30, and
        // lifetime clears the duration entirely.
        $termDays = (int) $this->input('term_days', 0);
        $termMonths = (int) $this->input('term_months', 0);
        $lifetime = (bool) $this->input('is_lifetime', false);

        $base = ($termDays > 0 ? $termDays : 0) + ($termMonths > 0 ? $termMonths * 30 : 0);

        $features = $this->parseFeatures($this->input('features')) ?? [];

        // Overlay the gated-feature toggles onto the features map so the
        // admin's checkbox choices land in the same stored JSON.
        $gateable = config('alumkit.membership.gateable_features', []);

        foreach (is_array($gateable) ? $gateable : [] as $key) {
            if ($this->boolean('feature_'.(string) $key)) {
                $features[(string) $key] = '1';
            }
        }

        $this->merge([
            'duration_days' => $lifetime ? null : $base,
            'is_lifetime' => $lifetime,
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'features' => $features === [] ? null : $features,
        ]);
    }

    /**
     * The features field is authored as newline-separated `key=value` lines and
     * stored as a flat string => string map.
     *
     * @return array<string, string>|null
     */
    protected function parseFeatures(mixed $input): ?array
    {
        if (is_array($input)) {
            $result = [];
            foreach ($input as $key => $value) {
                if (is_string($key) && $value !== null && $value !== '') {
                    $result[$key] = (string) $value;
                }
            }

            return $result === [] ? null : $result;
        }

        if (! is_string($input) || trim($input) === '') {
            return null;
        }

        $result = [];

        foreach (preg_split('/\R/', $input) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s*[=:]\s*/', $line, 2) ?: [];

            if (count($parts) === 2 && trim($parts[0]) !== '') {
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }

        return $result === [] ? null : $result;
    }
}
