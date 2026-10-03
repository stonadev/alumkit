@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.submit_payment'))

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::membership.submit_payment') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.membership.payments.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4" x-data="{ chosenMethod: @js(old('method')) }">
                <x-alumkit::select name="membership_plan_id" :label="__('alumkit::membership.plan')" :options="$plans->pluck('name', 'id')->all()" :value="old('membership_plan_id')" required />

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="amount" type="number" step="0.01" min="0" :label="__('alumkit::membership.amount')" :value="old('amount')" required />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('alumkit::membership.method') }}</label>
                        <select name="method" x-model="chosenMethod" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-navy focus:border-navy focus:ring-gold/50">
                            <option value="" disabled>{{ __('alumkit::membership.select_method') }}</option>
                            @foreach ($methods as $m)
                                <option value="{{ $m->type }}">{{ $m->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-lg border border-outline-variant/60 bg-surface-container/40 p-4" x-show="chosenMethod" x-cloak>
                    @foreach ($methods as $m)
                        <div x-show="chosenMethod === '{{ $m->type }}'" x-cloak>
                            <p class="font-semibold text-navy">{{ $m->label() }}</p>
                            @if ($m->instructions)
                                <div class="mt-1 text-sm text-gray-600">{!! $m->instructionsHtml() !!}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <x-alumkit::input-error name="method" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="reference" :label="__('alumkit::membership.reference')" :value="old('reference')" />
                    <x-input name="paid_at" type="date" :label="__('alumkit::membership.paid_at')" :value="old('paid_at', now()->format('Y-m-d'))" required />
                </div>

                <x-alumkit::textarea name="notes" :label="__('alumkit::membership.notes')" :value="old('notes')" />

                <x-input name="proof" type="file" :label="__('alumkit::membership.upload_proof')" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::membership.submit_payment')" />
                <a href="{{ route('alumkit.membership.show') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
