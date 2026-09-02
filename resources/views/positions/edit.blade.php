@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::committee.edit_position') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.positions.update', $position) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <x-input name="name" :label="__('alumkit::committee.position_name')" :value="$position->name" required />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::committee.edit_position')" />
                <a href="{{ route('alumkit.positions.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
