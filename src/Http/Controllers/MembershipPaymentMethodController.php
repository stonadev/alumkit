<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Http\Requests\StoreMembershipPaymentMethodRequest;
use Alumkit\Alumkit\Http\Requests\UpdateMembershipPaymentMethodRequest;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class MembershipPaymentMethodController extends Controller
{
    public function index(): View
    {
        $methods = MembershipPaymentMethod::orderBy('sort_order')->orderBy('type')->get();

        /** @var View $view */
        $view = view('alumkit::payment-methods.index', compact('methods'));

        return $view;
    }

    public function create(): View
    {
        /** @var View $view */
        $view = view('alumkit::payment-methods.create');

        return $view;
    }

    public function store(StoreMembershipPaymentMethodRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // New methods are appended to the end; staff re-order via drag-and-drop.
        $data['sort_order'] = (int) MembershipPaymentMethod::max('sort_order') + 1;

        MembershipPaymentMethod::create($data);

        return redirect()->route('alumkit.payment-methods.index')
            ->with('status', __('alumkit::membership.method_created'));
    }

    public function edit(MembershipPaymentMethod $paymentMethod): View
    {
        /** @var View $view */
        $view = view('alumkit::payment-methods.edit', compact('paymentMethod'));

        return $view;
    }

    public function update(UpdateMembershipPaymentMethodRequest $request, MembershipPaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update($request->validated());

        return redirect()->route('alumkit.payment-methods.index')
            ->with('status', __('alumkit::membership.method_updated'));
    }

    public function destroy(MembershipPaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->route('alumkit.payment-methods.index')
            ->with('status', __('alumkit::membership.method_deleted'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array']);

        foreach ($ids['ids'] as $position => $id) {
            MembershipPaymentMethod::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
