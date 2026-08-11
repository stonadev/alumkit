<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('alumkit::auth.dashboard') }} — {{ config('app.name', 'AlumKit') }}</title>
    @tallStackUiStyle
    <link rel="stylesheet" href="{{ url('alumkit/style/alumkit.css') }}">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-md flex flex-col h-screen sticky top-0">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ config('app.name', 'AlumKit') }}
                </h2>
            </div>

            <nav class="mt-4 flex-1">
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

                @can('manage careers')
                    <a href="{{ route('alumkit.careers.index') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ __('alumkit::career.careers') }}
                    </a>
                @endcan

                @if (auth()->user()->state === \Alumkit\Alumkit\Enums\UserState::Active->value)
                    <a href="{{ route('alumkit.posts.index') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ __('alumkit::post.posts') }}
                    </a>
                @endif

                @foreach (config('alumkit.dashboard_nav', []) as $item)
                    @if (! empty($item['children']) && is_array($item['children']))
                        @if (empty($item['permission']) || auth()->user()->can($item['permission']))
                            <p class="px-4 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ $item['label'] }}
                            </p>
                            @foreach ($item['children'] as $child)
                                @if (empty($child['permission']) || auth()->user()->can($child['permission']))
                                    <a href="{{ route($child['route']) }}" class="flex items-center pl-8 pr-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        {{ $child['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @elseif (! empty($item['route']))
                        @if (empty($item['permission']) || auth()->user()->can($item['permission']))
                            <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endif
                @endforeach

                <a href="{{ route('alumkit.profile') }}" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    {{ __('alumkit::auth.profile') }}
                </a>
            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
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
