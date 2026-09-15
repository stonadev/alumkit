<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\UserState;

final class SubmitProfileForReview
{
    /**
     * A registered or rejected member who submits their profile enters
     * the pending review queue for admin approval.
     */
    public function handle(mixed $user): void
    {
        $current = UserState::tryFrom((string) $user->state);

        if (! in_array($current, [UserState::Registered, UserState::Rejected], true)) {
            return;
        }

        $user->update(['state' => UserState::Pending->value]);

        $isResubmit = $current === UserState::Rejected;

        activity('profile')
            ->performedOn($user)
            ->event($isResubmit ? 'resubmitted' : 'submitted')
            ->log($isResubmit ? 'profile resubmitted for review' : 'profile submitted');
    }
}
