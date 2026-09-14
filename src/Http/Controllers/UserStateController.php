<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Notifications\UserRejectedNotification;
use Alumkit\Alumkit\Notifications\UserSuspendedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserStateController extends Controller
{
    public function update(Request $request, string $user): RedirectResponse
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');
        $targetUser = $userModel::findOrFail($user);

        $request->validate([
            'state' => ['required', 'string', 'in:'.implode(',', array_column(UserState::cases(), 'value'))],
            'reason' => ['required_if:state,rejected,suspended', 'nullable', 'string', 'max:2000'],
        ]);

        // Prevent state changes on unverified users: email must be verified before membership actions.
        if (is_null($targetUser->email_verified_at)) {
            return redirect()->route('alumkit.users.show', $targetUser)
                ->with('error', __('alumkit::dashboard.unverified_user_no_transition'));
        }

        // Prevent self-lockout: an admin cannot change their own membership state.
        if ($request->user()->getKey() === $targetUser->getKey()) {
            return redirect()->route('alumkit.users.show', $targetUser)
                ->with('error', __('alumkit::dashboard.cannot_change_own_state'));
        }

        $newState = UserState::from($request->input('state'));
        $currentState = UserState::from($targetUser->state);

        if (! $currentState->canTransitionTo($newState)) {
            return redirect()->route('alumkit.users.index')
                ->with('error', __('alumkit::dashboard.invalid_state_transition'));
        }

        $targetUser->update(['state' => $newState->value]);

        activity('member_management')
            ->performedOn($targetUser)
            ->event('state_changed')
            ->withProperties([
                'old_state' => $currentState->value,
                'new_state' => $newState->value,
                'reason' => $request->input('reason'),
            ])
            ->log('member state changed');

        if ($newState === UserState::Rejected) {
            $targetUser->notify(new UserRejectedNotification($request->input('reason')));
        } elseif ($newState === UserState::Suspended) {
            $targetUser->notify(new UserSuspendedNotification($request->input('reason')));
        }

        return redirect()->route('alumkit.users.index')
            ->with('status', __('alumkit::dashboard.user_state_updated'));
    }
}
