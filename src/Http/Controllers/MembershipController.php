<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Actions\CancelMembership;
use Alumkit\Alumkit\Actions\UpdateMembership;
use Alumkit\Alumkit\Http\Requests\UpdateMembershipRequest;
use Alumkit\Alumkit\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');

        $query = Membership::with(['user', 'plan'])->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $memberships = $query->paginate(20)->appends(['status' => $status]);

        /** @var View $view */
        $view = view('alumkit::memberships.index', compact('memberships', 'status'));

        return $view;
    }

    public function show(Membership $membership): View
    {
        $membership->load(['user', 'plan', 'payments.user', 'payments.reviewer']);

        /** @var View $view */
        $view = view('alumkit::memberships.show', compact('membership'));

        return $view;
    }

    public function update(UpdateMembershipRequest $request, Membership $membership, UpdateMembership $action): RedirectResponse
    {
        $action->handle($membership, $request->validated(), $request->user());

        return redirect()->route('alumkit.memberships.show', $membership)
            ->with('status', __('alumkit::membership.membership_updated'));
    }

    public function cancel(Request $request, Membership $membership, CancelMembership $action): RedirectResponse
    {
        $action->handle($membership, $request->user());

        return redirect()->route('alumkit.memberships.show', $membership)
            ->with('status', __('alumkit::membership.membership_cancelled'));
    }
}
