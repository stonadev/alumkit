<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Enums\UserState;

final class ResubmitProfileForReview
{
    /**
     * A rejected member who changes profile data returns to the pending
     * review queue so an admin re-reviews the submission.
     */
    public function handle(mixed $user): void
    {
        $current = UserState::tryFrom((string) $user->state);

        if (! $current?->canTransitionTo(UserState::Pending)) {
            return;
        }

        $user->update(['state' => UserState::Pending->value]);

        activity('profile')
            ->performedOn($user)
            ->event('resubmitted')
            ->log('profile resubmitted for review');
    }
}
