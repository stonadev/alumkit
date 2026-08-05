@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::career.add_career') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.careers.store') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('alumkit::career.employment_type') }}
                    </label>
                    <select name="employment_type" required class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        @foreach ($employmentTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('employment_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('employment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-input name="job_title" :label="__('alumkit::career.job_title')" :value="old('job_title')" required />
                <x-input name="company" :label="__('alumkit::career.company')" :value="old('company')" required />
                <x-input name="industry" :label="__('alumkit::career.industry')" :value="old('industry')" />
                <x-input name="location" :label="__('alumkit::career.location')" :value="old('location')" />

                <div class="grid grid-cols-2 gap-4" x-data="{ is_current: {{ old('is_current', false) ? 'true' : 'false' }} }">
                    <x-input type="number" name="start_year" :label="__('alumkit::career.start_year')" :value="old('start_year')" min="1900" max="2099" required />
                    <x-input type="number" name="start_month" :label="__('alumkit::career.start_month')" :value="old('start_month')" min="1" max="12" />
                </div>

                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_current" value="0">
                    <input type="checkbox" name="is_current" id="currently_working" value="1" x-model="is_current" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('alumkit::career.currently_working') }}</span>
                </label>

                <div class="grid grid-cols-2 gap-4" x-show="!is_current">
                    <x-input type="number" name="end_year" :label="__('alumkit::career.end_year')" :value="old('end_year')" min="1900" max="2099" />
                    <x-input type="number" name="end_month" :label="__('alumkit::career.end_month')" :value="old('end_month')" min="1" max="12" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('alumkit::career.description') }}
                    </label>
                    <textarea name="description" rows="4" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::career.add_career')" />
                <a href="{{ route('alumkit.careers.index') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
