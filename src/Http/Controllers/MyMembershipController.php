<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Enums\PaymentStatus;
use Alumkit\Alumkit\Events\PaymentSubmitted;
use Alumkit\Alumkit\Http\Requests\StoreMembershipPaymentRequest;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyMembershipController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $membership = $user->latestMembership()->with('plan')->first();
        $activeMembership = $user->activeMembership()->with('plan')->first();
        $payments = MembershipPayment::where('user_id', $user->getKey())
            ->with('plan')
            ->orderByDesc('id')
            ->paginate(10);
        $pendingPayment = MembershipPayment::where('user_id', $user->getKey())
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        /** @var View $view */
        $view = view('alumkit::membership.show', compact(
            'user',
            'membership',
            'activeMembership',
            'payments',
            'pendingPayment',
        ));

        return $view;
    }

    public function plans(): View
    {
        $plans = MembershipPlan::active()->get();

        /** @var View $view */
        $view = view('alumkit::membership.plans', compact('plans'));

        return $view;
    }

    public function createPayment(): View
    {
        $plans = MembershipPlan::active()->get();
        $methods = MembershipPaymentMethod::active()->get();

        /** @var View $view */
        $view = view('alumkit::membership.payment-create', compact('plans', 'methods'));

        return $view;
    }

    public function storePayment(StoreMembershipPaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $plan = MembershipPlan::findOrFail((int) $request->input('membership_plan_id'));

        $proofPath = null;

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store(
                'membership-payment-proofs',
                config('alumkit.membership.proof.disk', 'public'),
            );
            abort_unless(is_string($path), 500);
            $proofPath = $path;
        }

        $payment = MembershipPayment::create([
            'user_id' => $user->getKey(),
            'membership_plan_id' => $plan->getKey(),
            'amount' => $request->input('amount'),
            'method' => $request->input('method'),
            'reference' => $request->input('reference'),
            'paid_at' => $request->input('paid_at'),
            'proof_path' => $proofPath,
            'notes' => $request->input('notes'),
            'status' => PaymentStatus::Pending->value,
            'created_by' => $user->getKey(),
        ]);

        PaymentSubmitted::dispatch($payment);

        return redirect()->route('alumkit.membership.payments.show', $payment)
            ->with('status', __('alumkit::membership.payment_submitted'));
    }

    public function showPayment(Request $request, MembershipPayment $payment): View
    {
        abort_unless($payment->user_id === $request->user()->getKey(), 403);

        $payment->load(['plan', 'methodDetail']);

        /** @var View $view */
        $view = view('alumkit::membership.payment-show', compact('payment'));

        return $view;
    }

    public function proof(Request $request, MembershipPayment $payment): StreamedResponse
    {
        abort_unless($payment->user_id === $request->user()->getKey(), 403);

        $disk = config('alumkit.membership.proof.disk', 'public');
        $path = $payment->proof_path;

        abort_unless($path !== null && Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }
}
