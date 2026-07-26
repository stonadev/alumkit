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

        $newState = UserState::from($request->input('state'));
        $currentState = UserState::from($targetUser->state);

        if (! $currentState->canTransitionTo($newState)) {
            return redirect()->route('alumkit.users.index')
                ->with('error', __('alumkit::dashboard.invalid_state_transition'));
        }

        $targetUser->update(['state' => $newState->value]);

        return redirect()->route('alumkit.users.index')
            ->with('status', __('alumkit::dashboard.user_state_updated'));
    }
}
