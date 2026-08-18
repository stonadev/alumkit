<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Alumkit\Alumkit\Enums\UserState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->state === UserState::Suspended->value) {
            return redirect()->route('alumkit.dashboard');
        }

        return $next($request);
    }
}
