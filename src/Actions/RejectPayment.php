<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\PaymentStatus;
use Alumkit\Alumkit\Events\PaymentRejected;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\User;
use Alumkit\Alumkit\Notifications\MembershipRejectedNotification;
use Illuminate\Validation\ValidationException;

class RejectPayment
{
    /**
     * Staff rejection of a pending payment. A review reason is required.
     */
    public function handle(MembershipPayment $payment, string $reviewNotes, ?User $actor = null): void
    {
        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'payment' => __('alumkit::membership.payment_not_pending'),
            ]);
        }

        $payment->update([
            'status' => PaymentStatus::Rejected->value,
            'review_notes' => $reviewNotes,
            'reviewed_by' => $actor?->getKey(),
            'reviewed_at' => now(),
        ]);

        activity('memberships')
            ->performedOn($payment)
            ->event('payment_rejected')
            ->withProperties([
                'review_notes' => $reviewNotes,
            ])
            ->log('membership payment rejected');

        PaymentRejected::dispatch($payment);

        $userModel = config('alumkit.auth.user_model', User::class);
        $notifiable = $userModel::find($payment->user_id);

        $notifiable?->notify(new MembershipRejectedNotification($payment));
    }
}
