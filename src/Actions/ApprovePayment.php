<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\PaymentStatus;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovePayment
{
    public function __construct(
        private ActivateMembership $activate,
    ) {}

    /**
     * Staff approval of a pending payment. Approving runs the payment through
     * the single activation path (create or extend a membership).
     */
    public function handle(MembershipPayment $payment, ?User $actor = null): Membership
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'payment' => __('alumkit::membership.payment_not_pending'),
            ]);
        }

        return $this->activate->handle($payment, $actor);
    }
}
