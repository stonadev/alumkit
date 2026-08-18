<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Alumkit\Alumkit\Enums\UserState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserState
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! in_array($user->state, [UserState::Active->value, UserState::Pending->value, UserState::Rejected->value], true)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', __('alumkit::dashboard.account_suspended'));
        }

        return $next($request);
    }
}
