@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.payments'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::membership.payment_queue') }}
        </h1>

        <a href="{{ route('alumkit.payments.create') }}">
            <x-button :text="__('alumkit::membership.record_payment')" />
        </a>
    </div>

    <x-card>
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (['pending' => __('alumkit::membership.payment_pending'), 'approved' => __('alumkit::membership.payment_approved'), 'rejected' => __('alumkit::membership.payment_rejected'), 'all' => __('alumkit::membership.status')] as $key => $label)
                <a href="{{ route('alumkit.payments.index', ['status' => $key]) }}"
                   class="rounded px-3 py-1.5 text-sm {{ $status === $key ? 'bg-navy text-white' : 'bg-surface-container text-on-surface-variant hover:text-navy' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($payments->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::membership.no_payments') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.user_name') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.plan') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.amount') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.method') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.paid_at') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.status') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">
                                {{ $payment->user?->name ?? '—' }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $payment->plan?->name ?? '—' }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ \Alumkit\Alumkit\Facades\Alumkit::formatMoney($payment->amount) }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">{{ $payment->methodLabel() }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $payment->paid_at?->format('d M Y') }}</td>
                            <td class="py-3 px-4">
                                <span class="rounded px-2 py-0.5 text-xs font-medium {{ $payment->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($payment->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600') }}">
                                    {{ __('alumkit::membership.payment_'.$payment->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.payments.show', $payment) }}" class="text-navy hover:text-gold">
                                    {{ __('alumkit::dashboard.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                @include('alumkit.pagination::simple', ['paginator' => $payments])
            </div>
        @endif
    </x-card>
@endsection
