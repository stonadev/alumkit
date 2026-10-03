<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\MembershipStatus;
use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\User;
use Illuminate\Validation\ValidationException;

class CancelMembership
{
    /**
     * Cancel an active membership. No refund handling — payments are offline.
     */
    public function handle(Membership $membership, ?User $actor = null): Membership
    {
        if ($membership->status !== MembershipStatus::Active->value) {
            throw ValidationException::withMessages([
                'membership' => __('alumkit::membership.membership_not_active'),
            ]);
        }

        $membership->update([
            'status' => MembershipStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);

        activity('memberships')
            ->performedOn($membership)
            ->event('membership_cancelled')
            ->withProperties([
                'cancelled_by' => $actor?->getKey(),
            ])
            ->log('membership cancelled');

        return $membership;
    }
}
