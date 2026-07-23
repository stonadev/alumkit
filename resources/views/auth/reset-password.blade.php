@extends('alumkit::layouts.app')

@section('content')
    <x-card>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('alumkit::auth.reset_password') }}
            </h1>
        </div>

        <x-errors />

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-input
                type="email"
                name="email"
                :value="old('email', $request->email)"
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

            <div>
                <x-password
                    name="password_confirmation"
                    :label="__('alumkit::auth.confirm_password')"
                    required
                />
            </div>

            <x-button type="submit" block :text="__('alumkit::auth.reset_password')" />
        </form>
    </x-card>
@endsection
