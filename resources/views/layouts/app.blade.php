<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlumKit') }}</title>
    @tallStackUiStyle
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md p-6">
            @yield('content')
        </div>
    </div>
    @tallStackUiScript
    @livewireScripts
    @stack('scripts')
</body>
</html>
