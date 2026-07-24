@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('alumkit::dashboard.roles') }}
        </h1>

        @can('manage roles')
            <a href="{{ route('alumkit.roles.create') }}">
                <x-button :text="__('alumkit::dashboard.create_role')" />
            </a>
        @endcan
    </div>

    <x-card>
        @if ($roles->isEmpty())
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('alumkit::dashboard.no_roles') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.role_name') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.select_permissions') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="border-b dark:border-gray-700">
                            <td class="py-3 px-4 font-medium">{{ $role->name }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                                {{ trans_choice('alumkit::dashboard.permissions_count', $role->permissions->count(), ['count' => $role->permissions->count()]) }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @can('manage roles')
                                    <a href="{{ route('alumkit.roles.edit', $role) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ __('alumkit::dashboard.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('alumkit.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            {{ __('alumkit::dashboard.delete') }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
@endsection
