<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlumKit') }}</title>
    @tallStackUiStyle
    <link rel="stylesheet" href="{{ url('alumkit/style/alumkit.css') }}">
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md p-6">
            @yield('content')
        </div>
    </div>
    <script>
        window.alumkitForm = (rules, errors) => ({
            errors: errors || {},
            values: {},
            rules: rules || {},
            toKey(name) { return String(name || '').replace(/\[([^\]]+)\]/g, '.$1'); },
            fieldError(name) { return (this.errors[this.toKey(name)] || [])[0] || null; },
            isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); },
            isUrl(v) { try { const u = new URL(v); return u.protocol === 'http:' || u.protocol === 'https:'; } catch (e) { return false; } },
            validateField(name, value) {
                const key = this.toKey(name);
                this.values[key] = value;
                const r = this.rules[key];
                if (!r) return;
                let message = null;
                if (r.required && !String(value).trim()) { message = r.requiredMsg; }
                else if (value && r.email && !this.isEmail(value)) { message = r.emailMsg; }
                else if (value && r.url && !this.isUrl(value)) { message = r.urlMsg; }
                else if (value && r.min && String(value).length < r.min) { message = r.minMsg; }
                else if (r.confirmed && value !== this.values[r.confirmed]) { message = r.confirmedMsg; }
                if (message) { this.errors[key] = [message]; } else { delete this.errors[key]; }
                if (key === 'password' && this.values.password_confirmation !== undefined) {
                    this.validateField('password_confirmation', this.values.password_confirmation);
                }
            },
        });
    </script>
    @tallStackUiScript
    @livewireScripts
    @stack('scripts')
</body>
</html>
