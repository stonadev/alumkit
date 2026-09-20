<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\UserState;

final class SubmitProfileForReview
{
    /**
     * A rejected member who re-submits their profile enters
     * the pending review queue for admin approval.
     */
    public function handle(mixed $user): void
    {
        if (UserState::tryFrom((string) $user->state) !== UserState::Rejected) {
            return;
        }

        $user->update(['state' => UserState::Pending->value]);

        activity('profile')
            ->performedOn($user)
            ->event('resubmitted')
            ->log('profile resubmitted for review');
    }
}
