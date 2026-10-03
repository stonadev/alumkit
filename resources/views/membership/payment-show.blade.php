@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.payment'))

@section('content')
    <a href="{{ route('alumkit.membership.show') }}" class="mb-6 inline-block text-sm text-navy hover:text-gold">
        {{ __('alumkit::dashboard.back_to_dashboard') }}
    </a>

    <x-card>
        <p class="label-caps text-gold">{{ __('alumkit::membership.payment') }}</p>
        <h1 class="mt-2 font-serif text-2xl font-semibold text-navy">{{ $payment->plan?->name ?? '—' }}</h1>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="rounded px-2 py-0.5 text-xs font-medium {{ $payment->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($payment->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                {{ __('alumkit::membership.payment_'.$payment->status) }}
            </span>
        </div>

        <dl class="mt-6 space-y-3 border-t border-outline-variant/60 pt-5 text-sm">
            <div class="flex items-start justify-between gap-4">
                <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.amount') }}</dt>
                <dd class="text-right text-navy">{{ \Alumkit\Alumkit\Facades\Alumkit::formatMoney($payment->amount) }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
                <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.method') }}</dt>
                <dd class="text-right text-navy">{{ $payment->methodLabel() }}</dd>
            </div>
            @if ($payment->reference)
                <div class="flex items-start justify-between gap-4">
                    <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.reference') }}</dt>
                    <dd class="text-right text-navy">{{ $payment->reference }}</dd>
                </div>
            @endif
            <div class="flex items-start justify-between gap-4">
                <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.paid_at') }}</dt>
                <dd class="text-right text-navy">{{ $payment->paid_at?->format('d M Y') }}</dd>
            </div>
        </dl>

        @if ($payment->notes)
            <div class="mt-5 border-t border-outline-variant/60 pt-5">
                <p class="label-caps text-gold">{{ __('alumkit::membership.notes') }}</p>
                <p class="mt-2 text-sm text-on-surface-variant">{{ $payment->notes }}</p>
            </div>
        @endif

        @if ($payment->review_notes)
            <div class="mt-5 border-t border-outline-variant/60 pt-5">
                <p class="label-caps text-gold">{{ __('alumkit::membership.review_notes') }}</p>
                <p class="mt-2 text-sm text-on-surface-variant">{{ $payment->review_notes }}</p>
            </div>
        @endif

        @if ($payment->proof_path)
            <div class="mt-5 border-t border-outline-variant/60 pt-5">
                <p class="label-caps text-gold">{{ __('alumkit::membership.proof') }}</p>
                <a href="{{ route('alumkit.membership.payments.proof', $payment) }}" class="mt-2 inline-block text-navy hover:text-gold" target="_blank" rel="noopener">
                    {{ __('alumkit::membership.proof') }} →
                </a>
            </div>
        @endif
    </x-card>
@endsection
