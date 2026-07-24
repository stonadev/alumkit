<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('alumkit::auth.dashboard') }} — {{ config('app.name', 'AlumKit') }}</title>
    @tallStackUiStyle
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="relative w-64 bg-white dark:bg-gray-800 shadow-md">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ config('app.name', 'AlumKit') }}
                </h2>
            </div>

            <nav class="mt-4">
                <a href="{{ route('alumkit.dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('alumkit::dashboard.dashboard') }}
                </a>

                @can('manage roles')
                    <a href="{{ route('alumkit.roles.index') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ __('alumkit::dashboard.roles') }}
                    </a>
                @endcan

                @can('manage members')
                    <a href="{{ route('alumkit.users.index') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ __('alumkit::dashboard.manage_user_roles') }}
                    </a>
                @endcan

                <a href="{{ route('alumkit.profile') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('alumkit::auth.profile') }}
                </a>
            </nav>

            <div class="absolute bottom-0 w-64 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {{ Auth::user()->email }}
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button type="submit" block outline :text="__('alumkit::auth.logout')" />
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @tallStackUiScript
    @livewireScripts
</body>
</html>
