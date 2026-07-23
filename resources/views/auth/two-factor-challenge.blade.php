@extends('alumkit::layouts.app')

@section('content')
    <x-card>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('alumkit::auth.two_factor') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('alumkit::auth.two_factor_text') }}
            </p>
        </div>

        <x-errors />

        <form method="POST" action="{{ route('two-factor.login') }}" class="mt-6 space-y-4">
            @csrf

            <x-input
                type="text"
                name="code"
                :label="__('alumkit::auth.two_factor_code')"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
            />

            <x-button type="submit" block :text="__('alumkit::auth.sign_in')" />
        </form>

        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
            <form method="POST" action="{{ route('two-factor.login') }}">
                @csrf

                <x-input
                    type="text"
                    name="recovery_code"
                    :label="__('alumkit::auth.recovery_code')"
                    autocomplete="one-time-code"
                />

                <div class="mt-4">
                    <x-button type="submit" block outline :text="__('alumkit::auth.use_recovery_code')" />
                </div>
            </form>
        </div>
    </x-card>
@endsection
