<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Alumkit\Alumkit\Enums\UserState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->state !== UserState::Active->value) {
            abort(403);
        }

        return $next($request);
    }
}
