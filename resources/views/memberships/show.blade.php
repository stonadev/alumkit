@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.memberships'))

@section('content')
    <a href="{{ route('alumkit.memberships.index') }}" class="mb-6 inline-block text-sm text-navy hover:text-gold">
        {{ __('alumkit::dashboard.back_to_dashboard') }}
    </a>

    @php $effective = $membership->effectiveStatus(); @endphp

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-4">
            <x-card>
                <p class="label-caps text-gold">{{ __('alumkit::membership.current_membership') }}</p>
                <h1 class="mt-2 font-serif text-2xl font-semibold text-navy">{{ $membership->plan?->name ?? '—' }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="rounded px-2 py-0.5 text-xs font-medium {{ $effective->value === 'active' ? 'bg-emerald-100 text-emerald-800' : ($effective->value === 'expired' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ __('alumkit::membership.status_'.$effective->value) }}
                    </span>
                    @if ($membership->isLifetime())
                        <span class="rounded bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">{{ __('alumkit::membership.term_lifetime') }}</span>
                    @endif
                </div>

                <dl class="mt-6 space-y-3 border-t border-outline-variant/60 pt-5 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.starts_at') }}</dt>
                        <dd class="text-right text-navy">{{ $membership->starts_at?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::membership.ends_at') }}</dt>
                        <dd class="text-right text-navy">{{ $membership->ends_at?->format('d M Y') ?? __('alumkit::membership.never') }}</dd>
                    </div>
                    @if ($membership->user)
                        <div class="flex items-start justify-between gap-4">
                            <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::dashboard.user_name') }}</dt>
                            <dd class="text-right text-navy">{{ $membership->user->name }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($membership->notes)
                    <div class="mt-5 border-t border-outline-variant/60 pt-5">
                        <p class="label-caps text-gold">{{ __('alumkit::membership.notes') }}</p>
                        <p class="mt-2 text-sm text-on-surface-variant">{{ $membership->notes }}</p>
                    </div>
                @endif
            </x-card>

            <x-card>
                <p class="label-caps text-gold">{{ __('alumkit::dashboard.actions') }}</p>

                <form method="POST" action="{{ route('alumkit.memberships.update', $membership) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <x-input name="ends_at" type="date" :label="__('alumkit::membership.ends_at')" :value="old('ends_at', $membership->ends_at?->format('Y-m-d'))" />
                    <x-alumkit::textarea name="notes" :label="__('alumkit::membership.notes')" :value="old('notes', $membership->notes)" />
                    <x-button type="submit" :text="__('alumkit::membership.membership_updated')" />
                </form>

                @if ($effective->value === 'active')
                    <form method="POST" action="{{ route('alumkit.memberships.cancel', $membership) }}" class="mt-4" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
                        @csrf
                        <button type="submit" class="w-full rounded border border-error px-4 py-2 text-sm font-semibold text-error transition-colors hover:bg-error hover:text-white">
                            {{ __('alumkit::membership.membership_cancelled') }}
                        </button>
                    </form>
                @endif
            </x-card>
        </div>

        <div class="space-y-6 lg:col-span-8">
            <x-card>
                <p class="label-caps text-gold">{{ __('alumkit::membership.payments') }}</p>

                @if ($membership->payments->isEmpty())
                    <p class="mt-4 text-sm text-on-surface-variant">{{ __('alumkit::membership.no_payments') }}</p>
                @else
                    <table class="mt-4 w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 text-xs uppercase text-on-surface-variant">{{ __('alumkit::membership.paid_at') }}</th>
                                <th class="text-left py-2 px-3 text-xs uppercase text-on-surface-variant">{{ __('alumkit::membership.amount') }}</th>
                                <th class="text-left py-2 px-3 text-xs uppercase text-on-surface-variant">{{ __('alumkit::membership.method') }}</th>
                                <th class="text-left py-2 px-3 text-xs uppercase text-on-surface-variant">{{ __('alumkit::membership.status') }}</th>
                                <th class="text-right py-2 px-3 text-xs uppercase text-on-surface-variant">{{ __('alumkit::dashboard.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($membership->payments as $payment)
                                <tr class="border-b">
                                    <td class="py-2 px-3 text-sm text-navy">{{ $payment->paid_at?->format('d M Y') }}</td>
                                    <td class="py-2 px-3 text-sm text-navy">
                                        {{ \Alumkit\Alumkit\Facades\Alumkit::formatMoney($payment->amount) }}
                                    </td>
                                    <td class="py-2 px-3 text-sm text-on-surface-variant">{{ $payment->method }}</td>
                                    <td class="py-2 px-3 text-sm">
                                        <span class="rounded px-2 py-0.5 text-xs font-medium {{ $payment->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($payment->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                                            {{ __('alumkit::membership.payment_'.$payment->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <a href="{{ route('alumkit.payments.show', $payment) }}" class="text-navy hover:text-gold text-sm">
                                            {{ __('alumkit::dashboard.edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-card>
        </div>
    </div>
@endsection
