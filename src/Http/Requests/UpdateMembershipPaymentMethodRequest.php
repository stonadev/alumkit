<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Requests;

use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Illuminate\Validation\Rule;

/**
 * Updates share the store rules, but the `type` is locked: payments reference
 * a method by its type, so changing it would rewrite what history points at.
 * Ordering is drag-and-drop on the index, never edited here.
 */
class UpdateMembershipPaymentMethodRequest extends StoreMembershipPaymentMethodRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $method = $this->route('paymentMethod');
        $currentType = $method instanceof MembershipPaymentMethod ? $method->type : '';

        return array_merge(parent::rules(), [
            'type' => ['sometimes', Rule::in([$currentType])],
        ]);
    }
}
