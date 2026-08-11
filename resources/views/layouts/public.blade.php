<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlumKit') }}</title>
    @tallStackUiStyle
    <link rel="stylesheet" href="{{ url('alumkit/style/alumkit.css') }}">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <header class="bg-white dark:bg-gray-800 shadow-md">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900 dark:text-white">{{ config('app.name', 'AlumKit') }}</a>
            <a href="{{ route('alumkit.posts.public.index') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900">{{ __('alumkit::post.posts') }}</a>
        </div>
    </header>
    <main class="max-w-4xl mx-auto px-6 py-8">
        @yield('content')
    </main>
    @tallStackUiScript
    @livewireScripts
</body>
</html>
