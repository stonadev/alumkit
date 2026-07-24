@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::dashboard.manage_user_roles') }}
    </h1>

    <x-card>
        @if ($users->isEmpty())
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('alumkit::dashboard.no_users') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.user_email') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.roles') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-3 px-4 font-medium">{{ $u->email }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                                {{ $u->roles->pluck('name')->implode(', ') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.users.roles.edit', $u) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ __('alumkit::dashboard.assign_roles') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
@endsection
