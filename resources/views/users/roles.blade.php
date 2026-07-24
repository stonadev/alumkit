@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::dashboard.assign_roles') }}
    </h1>

    <x-card>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('alumkit::dashboard.user_email') }}: <strong>{{ $user->email }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('alumkit.users.roles.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('alumkit::dashboard.select_roles') }}
                </label>

                <div class="space-y-2">
                    @foreach ($roles as $role)
                        <x-checkbox
                            name="roles[]"
                            :label="$role->name"
                            :value="$role->name"
                            :checked="$user->hasRole($role)"
                        />
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::dashboard.assign_roles')" />
                <a href="{{ route('alumkit.dashboard') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
