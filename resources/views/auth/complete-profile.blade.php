@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.complete_profile')">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('alumkit::auth.complete_profile_text') }}
        </p>

        <form method="POST" action="{{ route('alumkit.profile.complete.store') }}" class="space-y-4">
            @csrf

            {{-- Education Section --}}
            <div
                x-data="{
                    educations: [{ level: '{{ array_key_first(config('alumkit.education.levels', [])) }}', institution: '', subject: '', start_year: '', start_month: '', end_year: '', end_month: '' }],
                    add() {
                        this.educations.push({ level: '{{ array_key_first(config('alumkit.education.levels', [])) }}', institution: '', subject: '', start_year: '', start_month: '', end_year: '', end_month: '' });
                    },
                    remove(index) {
                        this.educations.splice(index, 1);
                    }
                }"
                class="space-y-3"
            >
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::education.education') }}
                    </label>
                    <x-button
                        type="button"
                        x-on:click="add()"
                        :text="__('alumkit::education.add_education')"
                       xs
                       outline
                    />
                </div>

                <template x-for="(edu, index) in educations" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4 space-y-4 dark:border-gray-700">
                        <div class="flex justify-end" x-show="educations.length > 1">
                            <x-button
                                type="button"
                                x-on:click="remove(index)"
                                :text="__('alumkit::education.remove')"
                                xs
                                outline
                                color="red"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('alumkit::education.level') }}
                            </label>
                            <select
                                x-bind:name="'educations[' + index + '][level]'"
                                x-model="edu.level"
                                required
                                class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                            >
                                @foreach (config('alumkit.education.levels', []) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-input
                            type="text"
                            x-bind:name="'educations[' + index + '][institution]'"
                            x-model="edu.institution"
                            :label="__('alumkit::education.institution')"
                            required
                        />

                        <x-input
                            type="text"
                            x-bind:name="'educations[' + index + '][subject]'"
                            x-model="edu.subject"
                            :label="__('alumkit::education.subject')"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <x-input
                                type="number"
                                x-bind:name="'educations[' + index + '][start_year]'"
                                x-model="edu.start_year"
                                :label="__('alumkit::education.start_year')"
                                min="1900"
                                max="2099"
                            />
                            <x-input
                                type="number"
                                x-bind:name="'educations[' + index + '][start_month]'"
                                x-model="edu.start_month"
                                :label="__('alumkit::education.start_month')"
                                min="1"
                                max="12"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <x-input
                                type="number"
                                x-bind:name="'educations[' + index + '][end_year]'"
                                x-model="edu.end_year"
                                :label="__('alumkit::education.end_year')"
                                min="1900"
                                max="2099"
                            />
                            <x-input
                                type="number"
                                x-bind:name="'educations[' + index + '][end_month]'"
                                x-model="edu.end_month"
                                :label="__('alumkit::education.end_month')"
                                min="1"
                                max="12"
                            />
                        </div>
                    </div>
                </template>
            </div>

            <x-button type="submit" block :text="__('alumkit::auth.submit_for_approval')" />
        </form>

        @slot('footer')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" block outline :text="__('alumkit::auth.logout')" />
            </form>
        @endslot
    </x-alumkit::form-wrapper>
@endsection
