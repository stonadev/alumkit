@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.register')">
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <x-input
                type="email"
                name="email"
                :value="old('email')"
                :label="__('alumkit::auth.email')"
                required
            />

            <div>
                <x-password
                    name="password"
                    :label="__('alumkit::auth.password')"
                    required
                />
            </div>

            <div>
                <x-password
                    name="password_confirmation"
                    :label="__('alumkit::auth.confirm_password')"
                    required
                />
            </div>

            <x-button type="submit" block :text="__('alumkit::auth.register')" />
        </form>

        @slot('footer')
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('alumkit::auth.already_registered') }}
            </span>
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                {{ __('alumkit::auth.sign_in') }}
            </a>
        @endslot
    </x-alumkit::form-wrapper>
@endsection
