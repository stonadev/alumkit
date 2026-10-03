<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMembershipPaymentRequest extends FormRequest
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
            'membership_plan_id' => ['required', 'integer', 'exists:membership_plans,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'string', Rule::in($methodTypes)],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['nullable', 'file', 'mimes:'.$mimes, 'max:'.$maxKb],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $plan = MembershipPlan::find((int) $this->input('membership_plan_id'));

            if (! $plan instanceof MembershipPlan) {
                return;
            }

            if (! $plan->is_active) {
                $validator->errors()->add('membership_plan_id', __('alumkit::membership.plan_inactive'));

                return;
            }

            $expected = (string) $plan->price;
            $amount = number_format((float) $this->input('amount'), 2, '.', '');

            if ($amount !== $expected) {
                $validator->errors()->add('amount', __('alumkit::membership.amount_mismatch', ['expected' => $expected]));
            }
        });
    }
}
