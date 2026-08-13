@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::career.careers') }}
        </h1>

        @can('manage careers')
            <a href="{{ route('alumkit.careers.create') }}">
                <x-button :text="__('alumkit::career.add_career')" />
            </a>
        @endcan
    </div>

    <x-card>
        @if ($careers->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::career.no_careers') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::career.job_title') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::career.company') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::career.employment_type') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::career.period') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($careers as $career)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">
                                {{ $career->job_title }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $career->company }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ config("alumkit.career.employment_types.{$career->employment_type->value}", $career->employment_type->value) }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $career->start_year ?? '—' }}
                                @if ($career->start_month)
                                    / {{ $career->start_month }}
                                @endif
                                —
                                @if ($career->is_current)
                                    {{ __('alumkit::career.present') }}
                                @else
                                    {{ $career->end_year }}
                                    @if ($career->end_month)
                                        / {{ $career->end_month }}
                                    @endif
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @can('manage careers')
                                    <a href="{{ route('alumkit.careers.edit', $career) }}" class="text-navy hover:text-gold mr-3">
                                        {{ __('alumkit::dashboard.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('alumkit.careers.destroy', $career) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
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
