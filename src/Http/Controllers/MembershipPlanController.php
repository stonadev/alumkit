<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreMembershipPlanRequest;
use Alumkit\Alumkit\Http\Requests\UpdateMembershipPlanRequest;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MembershipPlanController extends Controller
{
    public function index(): View
    {
        $plans = MembershipPlan::orderBy('sort_order')->orderBy('id')->get();

        /** @var View $view */
        $view = view('alumkit::plans.index', compact('plans'));

        return $view;
    }

    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::plans.create');

        return $view;
    }

    public function store(StoreMembershipPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // New plans are appended to the end (unless sort_order is given);
        // staff re-order via drag-and-drop.
        $data['sort_order'] ??= (int) (MembershipPlan::max('sort_order') ?? -1) + 1;

        MembershipPlan::create($data);

        return redirect()->route('alumkit.plans.index')
            ->with('status', __('alumkit::membership.plan_created'));
    }

    public function edit(MembershipPlan $plan): View
    {
        /** @var View $view */
        $view = view('alumkit::plans.edit', compact('plan'));

        return $view;
    }

    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $plan): RedirectResponse
    {
        $data = $request->validated();

        $plan->update($data);

        return redirect()->route('alumkit.plans.index')
            ->with('status', __('alumkit::membership.plan_updated'));
    }

    public function destroy(MembershipPlan $plan): RedirectResponse
    {
        if ($plan->memberships()->exists() || $plan->payments()->exists()) {
            return redirect()->route('alumkit.plans.index')
                ->with('error', __('alumkit::membership.plan_in_use'));
        }

        $plan->delete();

        return redirect()->route('alumkit.plans.index')
            ->with('status', __('alumkit::membership.plan_deleted'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array']);

        foreach ($ids['ids'] as $position => $id) {
            MembershipPlan::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
