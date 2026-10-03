<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Http\Controllers;

use Alumkit\Alumkit\Actions\ApprovePayment;
use Alumkit\Alumkit\Actions\RejectPayment;
use Alumkit\Alumkit\Enums\PaymentStatus;
use Alumkit\Alumkit\Events\PaymentSubmitted;
use Alumkit\Alumkit\Http\Requests\RecordMembershipPaymentRequest;
use Alumkit\Alumkit\Http\Requests\RejectPaymentRequest;
use Alumkit\Alumkit\Models\MembershipPayment;
use Alumkit\Alumkit\Models\MembershipPaymentMethod;
use Alumkit\Alumkit\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', PaymentStatus::Pending->value);

        $query = MembershipPayment::with(['user', 'plan', 'methodDetail'])->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20)->appends(['status' => $status]);

        /** @var View $view */
        $view = view('alumkit::payments.index', compact('payments', 'status'));

        return $view;
    }

    public function create(): View
    {
        $userModel = config('alumkit.auth.user_model', 'App\\Models\\User');
        $plans = MembershipPlan::active()->get();
        $methods = MembershipPaymentMethod::active()->get();

        /** @var View $view */
        $view = view('alumkit::payments.create', ['plans' => $plans, 'methods' => $methods]);

        return $view;
    }

    public function store(RecordMembershipPaymentRequest $request, ApprovePayment $approve): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('membership-payment-proofs', config('alumkit.membership.proof.disk', 'public'));
            abort_unless(is_string($path), 500);
            $data['proof_path'] = $path;
        }

        unset($data['proof'], $data['activate']);

        $plan = MembershipPlan::findOrFail((int) $request->input('membership_plan_id'));

        $payment = MembershipPayment::create([
            'user_id' => $request->input('user_id'),
            'membership_plan_id' => $plan->getKey(),
            'amount' => $request->input('amount'),
            'method' => $request->input('method'),
            'reference' => $request->input('reference'),
            'paid_at' => $request->input('paid_at'),
            'proof_path' => $data['proof_path'] ?? null,
            'notes' => $request->input('notes'),
            'status' => PaymentStatus::Pending->value,
            'created_by' => $request->user()->getKey(),
        ]);

        PaymentSubmitted::dispatch($payment);

        if ($request->boolean('activate')) {
            $approve->handle($payment, $request->user());

            return redirect()->route('alumkit.memberships.show', $payment->membership_id)
                ->with('status', __('alumkit::membership.payment_recorded_and_approved'));
        }

        return redirect()->route('alumkit.payments.show', $payment)
            ->with('status', __('alumkit::membership.payment_recorded'));
    }

    public function show(MembershipPayment $payment): View
    {
        $payment->load(['user', 'plan', 'membership', 'reviewer', 'methodDetail']);

        /** @var View $view */
        $view = view('alumkit::payments.show', compact('payment'));

        return $view;
    }

    public function approve(Request $request, MembershipPayment $payment, ApprovePayment $approve): RedirectResponse
    {
        $membership = $approve->handle($payment, $request->user());

        return redirect()->route('alumkit.memberships.show', $membership)
            ->with('status', __('alumkit::membership.payment_approved'));
    }

    public function reject(RejectPaymentRequest $request, MembershipPayment $payment, RejectPayment $reject): RedirectResponse
    {
        $reject->handle($payment, $request->validated('review_notes'), $request->user());

        return redirect()->route('alumkit.payments.show', $payment)
            ->with('status', __('alumkit::membership.payment_rejected'));
    }

    public function proof(Request $request, MembershipPayment $payment): StreamedResponse
    {
        $user = $request->user();

        $allowed = $payment->user_id === $user->getKey() || $user->can('manage memberships');

        abort_unless($allowed, 403);

        $disk = config('alumkit.membership.proof.disk', 'public');
        $path = $payment->proof_path;

        abort_unless($path !== null && Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }
}
