@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">Global Content</h1>

    <x-card>
        @if (empty($globals))
            <p class="text-gray-600">No global content registered.</p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Key</th>
                        <th class="text-right py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($globals as $key => $schema)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">{{ $key }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.globals.edit', $key) }}" class="text-navy hover:text-gold">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
@endsection
