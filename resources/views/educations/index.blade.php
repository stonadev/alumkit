@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::education.educations') }}
        </h1>

        @can('manage educations')
            <a href="{{ route('alumkit.educations.create') }}">
                <x-button :text="__('alumkit::education.add_education')" />
            </a>
        @endcan
    </div>

    <x-card>
        @if ($educations->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::education.no_educations') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::education.level') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::education.institution') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::education.subject') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($educations as $education)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">
                                {{ config("alumkit.education.levels.{$education->level->value}", $education->level->value) }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $education->institution }}
                            </td>
                            <td class="py-3 px-4 text-gray-600">
                                {{ $education->subject ?? '—' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                @can('manage educations')
                                    <a href="{{ route('alumkit.educations.edit', $education) }}" class="text-navy hover:text-gold mr-3">
                                        {{ __('alumkit::dashboard.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('alumkit.educations.destroy', $education) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
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
