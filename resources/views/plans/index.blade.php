@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.plans'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::membership.plans') }}
        </h1>

        <a href="{{ route('alumkit.plans.create') }}">
            <x-button :text="__('alumkit::membership.new_plan')" />
        </a>
    </div>

    <x-card>
        @if ($plans->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::membership.no_plans') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.plan_name') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.term') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.price') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::membership.status') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">
                                {{ $plan->name }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $plan->termLabel() }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ \Alumkit\Alumkit\Facades\Alumkit::formatMoney($plan->price) }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($plan->is_active)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('alumkit::membership.active') }}</span>
                                @else
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ __('alumkit::membership.inactive') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.plans.edit', $plan) }}" class="text-navy hover:text-gold mr-3">
                                    {{ __('alumkit::dashboard.edit') }}
                                </a>

                                <form method="POST" action="{{ route('alumkit.plans.destroy', $plan) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        {{ __('alumkit::dashboard.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
@endsection
