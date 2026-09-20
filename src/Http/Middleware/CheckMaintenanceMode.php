<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('alumkit.maintenance.enabled')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $request->user() && in_array($routeName, ['login', 'register'], true)) {
            abort(503);
        }

        if ($request->user() && $routeName === 'alumkit.dashboard') {
            abort(503);
        }

        return $next($request);
    }
}
