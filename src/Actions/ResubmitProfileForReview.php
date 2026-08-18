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
        if ($user->state !== UserState::Rejected->value) {
            return;
        }

        $user->update(['state' => UserState::Pending->value]);
    }
}
