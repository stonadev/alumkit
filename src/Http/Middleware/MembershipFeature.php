<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipFeature
{
    /**
     * Gate a dashboard area behind a membership plan feature. While the
     * memberships feature is enabled, the user needs an active membership whose
     * plan grants `$feature` (or a membership-administration permission) to
     * proceed; otherwise they are sent to their membership page.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if ($user && ! $user->canAccessMembershipFeature($feature)) {
            return redirect()
                ->route('alumkit.membership.show')
                ->with('error', __('alumkit::membership.feature_locked', [
                    'feature' => __('alumkit::membership.feature_'.$feature),
                ]));
        }

        return $next($request);
    }
}
