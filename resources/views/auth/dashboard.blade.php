@extends('alumkit::layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold mb-4">{{ __('alumkit::auth.dashboard') }}</h1>

        <p class="text-gray-600 mb-6">
            {{ __('alumkit::auth.welcome', ['name' => Auth::user()->name]) }}
        </p>

        <form method="POST" action="{{ route('alumkit.logout') }}">
            @csrf
            <button
                type="submit"
                class="bg-gray-600 text-white rounded-md px-4 py-2 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            >
                {{ __('alumkit::auth.logout') }}
            </button>
        </form>
    </div>
@endsection
