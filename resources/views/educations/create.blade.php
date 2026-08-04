@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::education.add_education') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.educations.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('alumkit::education.level') }}
                    </label>
                    <select name="level" required class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        @foreach ($levels as $value => $label)
                            <option value="{{ $value }}" @selected(old('level') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-input name="institution" :label="__('alumkit::education.institution')" :value="old('institution')" required />
                <x-input name="subject" :label="__('alumkit::education.subject')" :value="old('subject')" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input type="number" name="start_year" :label="__('alumkit::education.start_year')" :value="old('start_year')" min="1900" max="2099" />
                    <x-input type="number" name="start_month" :label="__('alumkit::education.start_month')" :value="old('start_month')" min="1" max="12" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input type="number" name="end_year" :label="__('alumkit::education.end_year')" :value="old('end_year')" min="1900" max="2099" />
                    <x-input type="number" name="end_month" :label="__('alumkit::education.end_month')" :value="old('end_month')" min="1" max="12" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::education.add_education')" />
                <a href="{{ route('alumkit.educations.index') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
