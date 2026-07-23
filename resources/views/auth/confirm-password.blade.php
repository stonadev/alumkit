@extends('alumkit::layouts.app')

@section('content')
    <x-card>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('alumkit::auth.confirm_password_title') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('alumkit::auth.confirm_password_text') }}
            </p>
        </div>

        <x-errors />

        <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
            @csrf

            <x-password
                name="password"
                :label="__('alumkit::auth.password')"
                required
                autofocus
            />

            <x-button type="submit" block :text="__('alumkit::auth.confirm')" />
        </form>
    </x-card>
@endsection
