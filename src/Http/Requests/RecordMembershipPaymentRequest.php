<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordMembershipPaymentRequest extends FormRequest
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
        $methodTypes = MembershipPaymentMethod::active()->pluck('type')->all();
        $mimes = implode(',', (array) config('alumkit.membership.proof.mimes', ['jpg', 'jpeg', 'png', 'pdf']));
        $maxKb = (int) config('alumkit.membership.proof.max_kb', 2048);

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'membership_plan_id' => ['required', 'integer', 'exists:membership_plans,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'string', Rule::in($methodTypes)],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'activate' => ['sometimes', 'boolean'],
            'proof' => ['nullable', 'file', 'mimes:'.$mimes, 'max:'.$maxKb],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activate' => $this->boolean('activate'),
        ]);
    }
}
