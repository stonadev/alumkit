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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.start_year') }}</label>
                                <select x-bind:name="'educations[' + index + '][start_year]'" x-model="edu.start_year" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.start_month') }}</label>
                                <select x-bind:name="'educations[' + index + '][start_month]'" x-model="edu.start_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.end_year') }}</label>
                                <select x-bind:name="'educations[' + index + '][end_year]'" x-model="edu.end_year" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.end_month') }}</label>
                                <select x-bind:name="'educations[' + index + '][end_month]'" x-model="edu.end_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Career Section --}}
            <div
                x-data="{
                    careers: [{ job_title: '', company: '', employment_type: '{{ array_key_first($employmentTypes) }}', industry: '', location: '', start_year: '', start_month: '', is_current: false, end_year: '', end_month: '', description: '' }],
                    add() {
                        this.careers.push({ job_title: '', company: '', employment_type: '{{ array_key_first($employmentTypes) }}', industry: '', location: '', start_year: '', start_month: '', is_current: false, end_year: '', end_month: '', description: '' });
                    },
                    remove(index) {
                        this.careers.splice(index, 1);
                    }
                }"
                class="space-y-3"
            >
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::career.career') }}
                    </label>
                    <x-button
                        type="button"
                        x-on:click="add()"
                        :text="__('alumkit::career.add_career')"
                       xs
                       outline
                    />
                </div>

                <template x-for="(career, index) in careers" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4 space-y-4 dark:border-gray-700">
                        <div class="flex justify-end" x-show="careers.length > 1">
                            <x-button
                                type="button"
                                x-on:click="remove(index)"
                                :text="__('alumkit::education.remove')"
                                xs
                                outline
                                color="red"
                            />
                        </div>

                        <x-input
                            type="text"
                            x-bind:name="'careers[' + index + '][job_title]'"
                            x-model="career.job_title"
                            :label="__('alumkit::career.job_title')"
                            required
                        />

                        <x-input
                            type="text"
                            x-bind:name="'careers[' + index + '][company]'"
                            x-model="career.company"
                            :label="__('alumkit::career.company')"
                            required
                        />

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('alumkit::career.employment_type') }}
                            </label>
                            <select
                                x-bind:name="'careers[' + index + '][employment_type]'"
                                x-model="career.employment_type"
                                required
                                class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                            >
                                @foreach ($employmentTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <x-input
                            type="text"
                            x-bind:name="'careers[' + index + '][industry]'"
                            x-model="career.industry"
                            :label="__('alumkit::career.industry')"
                        />

                        <x-input
                            type="text"
                            x-bind:name="'careers[' + index + '][location]'"
                            x-model="career.location"
                            :label="__('alumkit::career.location')"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.start_year') }}</label>
                                <select x-bind:name="'careers[' + index + '][start_year]'" x-model="career.start_year" required class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.start_month') }}</label>
                                <select x-bind:name="'careers[' + index + '][start_month]'" x-model="career.start_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <label class="flex items-center gap-2">
                            <input type="hidden" x-bind:name="'careers[' + index + '][is_current]'" value="0">
                            <input
                                type="checkbox"
                                x-bind:name="'careers[' + index + '][is_current]'"
                                value="1"
                                x-model="career.is_current"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('alumkit::career.currently_working') }}</span>
                        </label>

                        <div class="grid grid-cols-2 gap-4" x-show="!career.is_current">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.end_year') }}</label>
                                <select x-bind:name="'careers[' + index + '][end_year]'" x-model="career.end_year" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.end_month') }}</label>
                                <select x-bind:name="'careers[' + index + '][end_month]'" x-model="career.end_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('alumkit::career.description') }}
                            </label>
                            <textarea
                                x-bind:name="'careers[' + index + '][description]'"
                                x-model="career.description"
                                rows="3"
                                class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                            ></textarea>
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
