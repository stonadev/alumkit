<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompleteProfileCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->profile()->exists() === false) {
            return redirect()->route('alumkit.profile.complete');
        }

        return $next($request);
    }
}
