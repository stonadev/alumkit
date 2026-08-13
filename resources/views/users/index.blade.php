@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::dashboard.manage_user_roles') }}
    </h1>

    <x-card>
        @if ($users->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::dashboard.no_users') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.user_name') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.user_email') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.state') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::dashboard.roles') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">{{ $u->name }}</td>
                            <td class="py-3 px-4">{{ $u->email }}</td>
                            <td class="py-3 px-4">
                                @include('alumkit::users.partials.state-badge', ['state' => $u->state])
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $u->roles->pluck('name')->implode(', ') }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                @php
                                    $currentState = \Alumkit\Alumkit\Enums\UserState::from($u->state);
                                    $transitions = $currentState->transitions();
                                @endphp
                                @foreach ($transitions as $transition)
                                    <form method="POST" action="{{ route('alumkit.users.state.update', $u) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="state" value="{{ $transition->value }}">
                                        <button type="submit" class="text-xs px-2 py-1 rounded
                                            @if($transition->value === 'active') bg-green-100 text-green-800 hover:bg-green-200
                                            @elseif($transition->value === 'suspended') bg-red-100 text-red-800 hover:bg-red-200
                                            @elseif($transition->value === 'rejected') bg-gray-100 text-gray-800 hover:bg-gray-200
                                            @endif
                                        ">
                                            {{ __("alumkit::dashboard.transition_to_{$transition->value}") }}
                                        </button>
                                    </form>
                                @endforeach
                                <a href="{{ route('alumkit.users.roles.edit', $u) }}" class="text-navy hover:text-gold">
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
