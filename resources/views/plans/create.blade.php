@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.new_plan'))

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::membership.new_plan') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.plans.store') }}">
            @csrf

            <div class="space-y-4">
                <x-input name="name" :label="__('alumkit::membership.plan_name')" :value="old('name')" required />
                <x-alumkit::textarea name="description" :label="__('alumkit::membership.description')" :value="old('description')" />

                <x-input name="price" type="number" step="0.01" min="0" :label="__('alumkit::membership.price')" :value="old('price', '0')" required />

                <div x-data="{ termType: @js(old('term_type', 'days')) }">
                    <p class="block text-sm font-medium text-gray-700 mb-1">{{ __('alumkit::membership.term') }}</p>

                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="term_type" value="days" x-model="termType" class="text-navy focus:ring-gold/50">
                            {{ __('alumkit::membership.term_unit_days') }}
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="term_type" value="months" x-model="termType" class="text-navy focus:ring-gold/50">
                            {{ __('alumkit::membership.term_unit_months') }}
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="term_type" value="lifetime" x-model="termType" class="text-navy focus:ring-gold/50">
                            {{ __('alumkit::membership.is_lifetime') }}
                        </label>
                    </div>

                    <template x-if="termType === 'days'">
                        <div class="mt-3">
                            <x-input name="term_days" type="number" min="1" :label="__('alumkit::membership.duration_days')" :value="old('term_days')" />
                        </div>
                    </template>
                    <template x-if="termType === 'months'">
                        <div class="mt-3">
                            <x-input name="term_months" type="number" min="1" :label="__('alumkit::membership.duration_months')" :value="old('term_months')" />
                        </div>
                    </template>
                    <p class="mt-2 text-sm text-gray-500" x-show="termType === 'lifetime'" x-cloak>
                        {{ __('alumkit::membership.term_lifetime_note') }}
                    </p>

                    <input type="hidden" name="is_lifetime" :value="termType === 'lifetime' ? '1' : '0'">
                    <x-alumkit::input-error name="term_days" />
                    <x-alumkit::input-error name="term_months" />
                </div>

                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-1">{{ __('alumkit::membership.feature_gating') }}</p>
                    <div class="flex flex-wrap items-center gap-4">
                        @foreach (config('alumkit.membership.gateable_features', []) as $key)
                            <x-alumkit::checkbox name="feature_{{ $key }}" :label="__('alumkit::membership.feature_'.$key)" :checked="old('feature_'.$key, false)" />
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('alumkit::membership.feature_gating_help') }}</p>
                </div>

                <x-alumkit::textarea name="features" :label="__('alumkit::membership.features')" :value="old('features')" :placeholder="__('alumkit::membership.features_help')" />

                <x-alumkit::checkbox name="is_active" :label="__('alumkit::membership.is_active')" :checked="old('is_active', true)" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::membership.plan_created')" />
                <a href="{{ route('alumkit.plans.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
