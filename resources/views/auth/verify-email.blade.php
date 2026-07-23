@extends('alumkit::layouts.app')

@section('content')
    <x-card>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('alumkit::auth.verify_email') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('alumkit::auth.verify_email_resent') }}
            </p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="mt-4 text-sm text-green-600 dark:text-green-400">
                {{ __('alumkit::auth.verify_email_sent') }}
            </div>
        @endif

        <div class="mt-6 space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button type="submit" block :text="__('alumkit::auth.resend_verification')" />
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" block outline :text="__('alumkit::auth.logout')" />
            </form>
        </div>
    </x-card>
@endsection
