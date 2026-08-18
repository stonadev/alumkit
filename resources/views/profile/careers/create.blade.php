@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::career.add_career') }}
    </h1>

    <x-card>
        <x-errors />

        <form method="POST" action="{{ route('alumkit.profile.careers.store') }}">
            @csrf

            <div class="space-y-4" x-data="{ is_current: {{ old('is_current', false) ? 'true' : 'false' }} }">
                <x-alumkit::select name="employment_type" :label="__('alumkit::career.employment_type')" :options="$employmentTypes" :value="old('employment_type')" required />

                <x-input name="job_title" :label="__('alumkit::career.job_title')" :value="old('job_title')" required />
                <x-input name="company" :label="__('alumkit::career.company')" :value="old('company')" required />
                <x-input name="industry" :label="__('alumkit::career.industry')" :value="old('industry')" />
                <x-input name="location" :label="__('alumkit::career.location')" :value="old('location')" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input type="number" name="start_year" :label="__('alumkit::career.start_year')" :value="old('start_year')" min="1900" max="2099" required />
                    <x-input type="number" name="start_month" :label="__('alumkit::career.start_month')" :value="old('start_month')" min="1" max="12" />
                </div>

                <x-alumkit::checkbox name="is_current" :label="__('alumkit::career.currently_working')" x-model="is_current" />

                <div class="grid grid-cols-2 gap-4" x-show="!is_current">
                    <x-input type="number" name="end_year" :label="__('alumkit::career.end_year')" :value="old('end_year')" min="1900" max="2099" />
                    <x-input type="number" name="end_month" :label="__('alumkit::career.end_month')" :value="old('end_month')" min="1" max="12" />
                </div>

                <x-alumkit::textarea name="description" :label="__('alumkit::career.description')" :value="old('description')" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::career.add_career')" />
                <a href="{{ route('alumkit.profile').'#career' }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
