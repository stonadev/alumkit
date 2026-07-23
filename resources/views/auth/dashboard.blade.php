@extends('alumkit::layouts.app')

@section('content')
    <x-card>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('alumkit::auth.dashboard') }}
            </h1>
        </div>

        <p class="mt-4 text-gray-600 dark:text-gray-400">
            {{ __('alumkit::auth.welcome', ['email' => Auth::user()->email]) }}
        </p>

        <div class="mt-6 space-y-2">
            <a href="{{ route('alumkit.profile') }}">
                <x-button block :text="__('alumkit::auth.profile')" />
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" block outline :text="__('alumkit::auth.logout')" />
            </form>
        </div>
    </x-card>
@endsection
