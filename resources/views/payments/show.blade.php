@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.payment'))

@section('content')
    <a href="{{ route('alumkit.payments.index') }}" class="mb-6 inline-block text-sm text-navy hover:text-gold">
        {{ __('alumkit::dashboard.back_to_dashboard') }}
    </a>

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-7">
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
                        <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::dashboard.user_name') }}</dt>
                        <dd class="text-right text-navy">{{ $payment->user?->name ?? '—' }}</dd>
                    </div>
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
                    @if ($payment->reviewed_at)
                        <div class="flex items-start justify-between gap-4">
                            <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.reviewed_at') }}</dt>
                            <dd class="text-right text-navy">{{ $payment->reviewed_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
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
                        <a href="{{ route('alumkit.payments.proof', $payment) }}" class="mt-2 inline-block text-navy hover:text-gold" target="_blank" rel="noopener">
                            {{ __('alumkit::membership.proof') }} →
                        </a>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="space-y-6 lg:col-span-5">
            @if ($payment->status === 'pending')
                <x-card>
                    <p class="label-caps text-gold">{{ __('alumkit::dashboard.actions') }}</p>

                    <form method="POST" action="{{ route('alumkit.payments.approve', $payment) }}" class="mt-4">
                        @csrf
                        <x-button type="submit" :text="__('alumkit::membership.approve')" />
                    </form>

                    <form method="POST" action="{{ route('alumkit.payments.reject', $payment) }}" class="mt-4 space-y-3">
                        @csrf
                        <x-alumkit::textarea name="review_notes" :label="__('alumkit::membership.review_notes')" :value="old('review_notes')" required />
                        <button type="submit" class="w-full rounded border border-error px-4 py-2 text-sm font-semibold text-error transition-colors hover:bg-error hover:text-white">
                            {{ __('alumkit::membership.reject') }}
                        </button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
@endsection
