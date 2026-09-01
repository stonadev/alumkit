<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\UserState;
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
        ]);

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
            ->withProperties(['old_state' => $currentState->value, 'new_state' => $newState->value])
            ->log('member state changed');

        return redirect()->route('alumkit.users.index')
            ->with('status', __('alumkit::dashboard.user_state_updated'));
    }
}
