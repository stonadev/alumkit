@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.sign_in')">
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <x-input
                type="email"
                name="email"
                :value="old('email')"
                :label="__('alumkit::auth.email')"
                required
                autofocus
            />

            <div>
                <x-password
                    name="password"
                    :label="__('alumkit::auth.password')"
                    required
                />
            </div>

            <div class="flex items-center justify-between">
                <x-checkbox name="remember" :label="__('alumkit::auth.remember_me')" />

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                        {{ __('alumkit::auth.forgot_password') }}
                    </a>
                @endif
            </div>

            <x-button type="submit" block :text="__('alumkit::auth.sign_in')" />
        </form>

        @slot('footer')
            @if (Route::has('register'))
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('alumkit::auth.no_account') }}
                </span>
                <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                    {{ __('alumkit::auth.register') }}
                </a>
            @endif
        @endslot
    </x-alumkit::form-wrapper>
@endsection
