@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::education.update_education') }}
    </h1>

    <x-card>
        <x-errors />

        <form method="POST" action="{{ route('alumkit.profile.educations.update', $education) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <x-alumkit::suggest name="level" :label="__('alumkit::education.level')" :value="old('level', $education->level)" :suggestions="config('alumkit.education.levels', [])" required />

                <x-alumkit::suggest name="institution" :label="__('alumkit::education.institution')" :value="old('institution', $education->institution)" :suggestions="config('alumkit.education.institutions', [])" required />
                <x-alumkit::suggest name="subject" :label="__('alumkit::education.subject')" :value="old('subject', $education->subject)" :suggestions="config('alumkit.education.subjects', [])" />
                <x-input type="text" name="student_id" :label="__('alumkit::education.student_id')" :value="old('student_id', $education->student_id)" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input type="number" name="start_year" :label="__('alumkit::education.start_year')" :value="old('start_year', $education->start_year)" min="1900" max="2099" />
                    <x-input type="number" name="start_month" :label="__('alumkit::education.start_month')" :value="old('start_month', $education->start_month)" min="1" max="12" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input type="number" name="end_year" :label="__('alumkit::education.end_year')" :value="old('end_year', $education->end_year)" min="1900" max="2099" />
                    <x-input type="number" name="end_month" :label="__('alumkit::education.end_month')" :value="old('end_month', $education->end_month)" min="1" max="12" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::education.update_education')" />
                <a href="{{ route('alumkit.profile').'#education' }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
