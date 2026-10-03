<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\MembershipStatus;
use Alumkit\Alumkit\Enums\PaymentStatus;
use Alumkit\Alumkit\Events\MembershipActivated;
use Alumkit\Alumkit\Events\PaymentApproved;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\User;
use Alumkit\Alumkit\Notifications\MembershipActivatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivateMembership
{
    /**
     * The single activation writer. Creates or extends a membership from an
     * approved payment. Idempotent per payment: a payment already linked to a
     * membership is never applied twice.
     */
    public function handle(MembershipPayment $payment, ?User $actor = null): Membership
    {
        if ($payment->membership_id !== null) {
            throw ValidationException::withMessages([
                'payment' => __('alumkit::membership.already_activated'),
            ]);
        }

        if ($payment->status !== PaymentStatus::Pending->value && $payment->status !== PaymentStatus::Approved->value) {
            throw ValidationException::withMessages([
                'payment' => __('alumkit::membership.payment_not_activatable'),
            ]);
        }

        return DB::transaction(function () use ($payment, $actor): Membership {
            $plan = $payment->plan()->lockForUpdate()->firstOrFail();

            $userModel = config('alumkit.auth.user_model', User::class);
            $user = $userModel::findOrFail($payment->user_id);

            $existing = Membership::where('user_id', $user->getKey())
                ->where('status', MembershipStatus::Active->value)
                ->lockForUpdate()
                ->first();

            $isNew = false;

            if ($existing === null) {
                $startsAt = $payment->paid_at ?? now()->startOfDay();
                $endsAt = $plan->termEndFrom($startsAt);

                $membership = Membership::create([
                    'user_id' => $user->getKey(),
                    'membership_plan_id' => $plan->getKey(),
                    'status' => MembershipStatus::Active->value,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'created_by' => $actor?->getKey(),
                ]);

                $isNew = true;
            } else {
                // Lifetime memberships are never shortened or downgraded.
                if ($existing->isLifetime()) {
                    $membership = $existing;
                } else {
                    $base = now()->startOfDay();

                    if ($existing->ends_at !== null && $existing->ends_at->isFuture()) {
                        $base = $existing->ends_at->copy()->startOfDay();
                    }

                    $endsAt = $plan->termEndFrom($base);

                    $existing->update([
                        'membership_plan_id' => $plan->getKey(),
                        'ends_at' => $endsAt,
                    ]);

                    $membership = $existing;
                }
            }

            $payment->update([
                'status' => PaymentStatus::Approved->value,
                'reviewed_by' => $actor?->getKey(),
                'reviewed_at' => now(),
                'membership_id' => $membership->getKey(),
            ]);

            activity('memberships')
                ->performedOn($payment)
                ->event('payment_approved')
                ->withProperties([
                    'membership_id' => $membership->getKey(),
                ])
                ->log('membership payment approved');

            PaymentApproved::dispatch($payment, $membership);

            if ($isNew) {
                MembershipActivated::dispatch($membership);
            }

            $user->notify(new MembershipActivatedNotification($membership));

            return $membership;
        });
    }
}
