@extends('alumkit::layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center mb-6">{{ __('alumkit::auth.sign_in') }}</h1>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('alumkit::auth.email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('alumkit::auth.password') }}
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">{{ __('alumkit::auth.remember_me') }}</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 text-white rounded-md px-4 py-2 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('alumkit::auth.sign_in') }}
            </button>
        </form>
    </div>
@endsection
