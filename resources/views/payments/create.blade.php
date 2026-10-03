@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.record_payment'))

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::membership.record_payment') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.payments.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4">
                <x-alumkit::user-search name="user_id" :label="__('alumkit::dashboard.select_user')" />

                <x-alumkit::select name="membership_plan_id" :label="__('alumkit::membership.plan')" :options="$plans->pluck('name', 'id')->all()" :value="old('membership_plan_id')" required />

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="amount" type="number" step="0.01" min="0" :label="__('alumkit::membership.amount')" :value="old('amount')" required />
                    <x-alumkit::select name="method" :label="__('alumkit::membership.method')" :options="$methods->mapWithKeys(fn ($m) => [$m->type => $m->label()])->all()" :value="old('method')" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input name="reference" :label="__('alumkit::membership.reference')" :value="old('reference')" />
                    <x-input name="paid_at" type="date" :label="__('alumkit::membership.paid_at')" :value="old('paid_at', now()->format('Y-m-d'))" required />
                </div>

                <x-alumkit::textarea name="notes" :label="__('alumkit::membership.notes')" :value="old('notes')" />

                <x-input name="proof" type="file" :label="__('alumkit::membership.upload_proof')" />

                <x-alumkit::checkbox name="activate" label="Activate membership immediately" :checked="old('activate')" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::membership.record_payment')" />
                <a href="{{ route('alumkit.payments.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
