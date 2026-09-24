<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Listeners;

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Models\User;
use Illuminate\Auth\Events\Verified;

class MarkUserPendingOnVerification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user instanceof User && $user->state === UserState::Unverified->value) {
            // Transition from Unverified → Pending so the user enters
            // the admin approval queue before gaining full access.
            $user->update(['state' => UserState::Pending->value]);
        }
    }
}
