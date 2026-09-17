@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.complete_profile')" :show-errors="false">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('alumkit::auth.complete_profile_text') }}
        </p>

        @php
            // Restore typed values on validation failure; the education step
            // always starts with at least one empty row.
            $defaultEducation = ['level' => '', 'institution' => '', 'student_id' => '', 'subject' => '', 'start_year' => '', 'start_month' => '', 'is_current' => false, 'end_year' => '', 'end_month' => ''];
            $oldEducations = array_map(
                fn (array $e): array => array_merge($defaultEducation, $e),
                old('educations', []) ?: [$defaultEducation],
            );
            $oldCareers = array_map(function (array $c): array {
                $c['is_current'] = ($c['is_current'] ?? false) == '1';

                return array_merge([
                    'job_title' => '', 'company' => '', 'employment_type' => '', 'industry' => '', 'location' => '',
                    'start_year' => '', 'start_month' => '', 'is_current' => false, 'end_year' => '', 'end_month' => '', 'description' => '',
                ], $c);
            }, old('careers', []));

            $initialStep = 1;
            if ($errors->isNotEmpty()) {
                $keys = array_keys($errors->getMessages());
                if (collect($keys)->contains(fn (string $k): bool => str_starts_with($k, 'educations'))) {
                    $initialStep = 1;
                } elseif (collect($keys)->contains(fn (string $k): bool => str_starts_with($k, 'careers'))) {
                    $initialStep = 2;
                } else {
                    $initialStep = 3;
                }
            }

            $localNameRules = [];
            foreach (config('alumkit.local_names', []) as $code => $langConfig) {
                if ($langConfig['required'] ?? false) {
                    $localNameRules["local_names.{$code}"] = [
                        'required' => true,
                        'requiredMsg' => __('validation.required', ['attribute' => $langConfig['label']]),
                    ];
                }
            }
        @endphp

        <form method="POST" action="{{ route('alumkit.profile.complete.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="{
            step: {{ $initialStep }},
            educations: {{ Js::from($oldEducations) }},
            careers: {{ Js::from($oldCareers) }},
            errors: {{ Js::from($errors->getMessages()) }},
            eager: {},
            localNameRules: {{ Js::from($localNameRules) }},
            toKey(name) { return String(name || '').replace(/\[([^\]]+)\]/g, '.$1'); },
            fieldError(key) { return (this.errors[this.toKey(key)] || [])[0] || null; },
            validateLocalNames(e) {
                const key = this.toKey(e.target.name);
                const r = this.localNameRules[key];
                if (!r) return;
                if (r.required && !String(e.target.value).trim()) { this.errors[key] = [r.requiredMsg]; }
                else { delete this.errors[key]; }
            },
            validateStep(s) {
                this.clearStepErrors(s);
                let valid = true;
                if (s === 1) {
                    this.educations.forEach((edu, i) => {
                        const p = 'educations.' + i + '.';
                        if (!edu.level) { this.errors[p + 'level'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::education.level')])) }}]; valid = false; }
                        if (!edu.institution) { this.errors[p + 'institution'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::education.institution')])) }}]; valid = false; }
                        if (!edu.subject) { this.errors[p + 'subject'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::education.subject')])) }}]; valid = false; }
                        if (!edu.start_year) { this.errors[p + 'start_year'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::education.start_year')])) }}]; valid = false; }
                        if (!edu.is_current && !edu.end_year) { this.errors[p + 'end_year'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::education.end_year')])) }}]; valid = false; }
                    });
                } else if (s === 2) {
                    this.careers.forEach((c, i) => {
                        const p = 'careers.' + i + '.';
                        if (!c.job_title) { this.errors[p + 'job_title'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.job_title')])) }}]; valid = false; }
                        if (!c.company) { this.errors[p + 'company'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.company')])) }}]; valid = false; }
                        if (!c.employment_type) { this.errors[p + 'employment_type'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.employment_type')])) }}]; valid = false; }
                        if (!c.start_year) { this.errors[p + 'start_year'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.start_year')])) }}]; valid = false; }
                    });
                }
                return valid;
            },
            clearStepErrors(s) {
                const prefix = s === 1 ? 'educations.' : 'careers.';
                Object.keys(this.errors).forEach((k) => { if (k.startsWith(prefix)) delete this.errors[k]; });
            },
            attemptStep(s) {
                this.eager[s] = true;
                if (this.validateStep(s)) { this.step++; }
            },
            liveValidate() {
                if (this.eager[this.step]) { this.validateStep(this.step); }
            },
            addEducation() {
                this.educations.push({ level: '', institution: '', student_id: '', subject: '', start_year: '', start_month: '', is_current: false, end_year: '', end_month: '' });
            },
            removeEducation(i) { this.educations.splice(i, 1); },
            addCareer() {
                this.careers.push({ job_title: '', company: '', employment_type: '{{ array_key_first($employmentTypes) }}', industry: '', location: '', start_year: '', start_month: '', is_current: false, end_year: '', end_month: '', description: '' });
            },
            removeCareer(i) { this.careers.splice(i, 1); }
        }" x-ref="form">
            @csrf

            <ol aria-label="{{ __('alumkit::auth.complete_profile') }}" class="mx-auto mb-6 flex w-full max-w-xs items-center text-xs">
                {{-- Node 1: Education --}}
                <li class="flex flex-1 items-center" :aria-current="step === 1 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step > 1 ? 'border-navy bg-navy text-white' : (step === 1 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400')">
                        <span x-show="step > 1" x-cloak>&#10003;</span>
                        <span x-show="step <= 1" x-cloak>1</span>
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 1 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::education.education') }}</span>
                    <span class="mx-2 h-px flex-1" :class="step > 1 ? 'bg-navy' : 'bg-gray-300'"></span>
                </li>
                {{-- Node 2: Career --}}
                <li class="flex flex-1 items-center" :aria-current="step === 2 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step > 2 ? 'border-navy bg-navy text-white' : (step === 2 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400')">
                        <span x-show="step > 2" x-cloak>&#10003;</span>
                        <span x-show="step <= 2" x-cloak>2</span>
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 2 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::career.career') }}</span>
                    <span class="mx-2 h-px flex-1" :class="step > 2 ? 'bg-navy' : 'bg-gray-300'"></span>
                </li>
                {{-- Node 3: Profile Details --}}
                <li class="flex items-center" :aria-current="step === 3 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step === 3 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400'">
                        3
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 3 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::profile.details') }}</span>
                </li>
            </ol>

            <p class="mb-4 text-center text-xs text-gray-500 sm:hidden">{{ __('alumkit::auth.step') }} <span x-text="step" class="font-semibold text-navy"></span> {{ __('alumkit::auth.of') }} 3</p>

            {{-- Education Section --}}
            <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.enter="event.target.tagName === 'TEXTAREA' || (event.preventDefault(), attemptStep(1))" @focusout="liveValidate()">
            <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('alumkit::education.education') }}
                </label>

                <template x-for="(edu, index) in educations" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4 space-y-4 dark:border-gray-700">
                        <div class="flex justify-end" x-show="educations.length > 1">
                            <x-button
                                type="button"
                                x-on:click="removeEducation(index)"
                                :text="__('alumkit::education.remove')"
                                xs
                                outline
                                color="red"
                            />
                        </div>

                        <div>
                            <x-alumkit::suggest
                                x-bind:name="'educations[' + index + '][level]'"
                                x-model="edu.level"
                                :label="__('alumkit::education.level')"
                                :suggestions="config('alumkit.education.levels', [])"
                                required
                            />
                            <p x-show="fieldError('educations.' + index + '.level')" x-cloak
                               x-text="fieldError('educations.' + index + '.level')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-alumkit::suggest
                                x-bind:name="'educations[' + index + '][institution]'"
                                x-model="edu.institution"
                                :label="__('alumkit::education.institution')"
                                :suggestions="config('alumkit.education.institutions', [])"
                                required
                            />
                            <p x-show="fieldError('educations.' + index + '.institution')" x-cloak
                               x-text="fieldError('educations.' + index + '.institution')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-alumkit::suggest
                                x-bind:name="'educations[' + index + '][subject]'"
                                x-model="edu.subject"
                                :label="__('alumkit::education.subject')"
                                :suggestions="config('alumkit.education.subjects', [])"
                                required
                            />
                            <p x-show="fieldError('educations.' + index + '.subject')" x-cloak
                               x-text="fieldError('educations.' + index + '.subject')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                x-bind:name="'educations[' + index + '][student_id]'"
                                x-model="edu.student_id"
                                :label="__('alumkit::education.student_id')"
                            />
                            <p x-show="fieldError('educations.' + index + '.student_id')" x-cloak
                               x-text="fieldError('educations.' + index + '.student_id')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.start_year') }}</label>
                                <select x-bind:name="'educations[' + index + '][start_year]'" x-model="edu.start_year" required class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('educations.' + index + '.start_year')" x-cloak
                                   x-text="fieldError('educations.' + index + '.start_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.start_month') }}</label>
                                <select x-bind:name="'educations[' + index + '][start_month]'" x-model="edu.start_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('educations.' + index + '.start_month')" x-cloak
                                   x-text="fieldError('educations.' + index + '.start_month')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                        </div>

                        <label class="flex items-center gap-2">
                            <input type="hidden" x-bind:name="'educations[' + index + '][is_current]'" value="0">
                            <input type="checkbox" x-bind:name="'educations[' + index + '][is_current]'" value="1"
                                   x-model="edu.is_current" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('alumkit::education.currently_studying') }}</span>
                        </label>

                        <div class="grid grid-cols-2 gap-4" x-show="!edu.is_current">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.end_year') }}</label>
                                <select x-bind:name="'educations[' + index + '][end_year]'" x-model="edu.end_year" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('educations.' + index + '.end_year')" x-cloak
                                   x-text="fieldError('educations.' + index + '.end_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::education.end_month') }}</label>
                                <select x-bind:name="'educations[' + index + '][end_month]'" x-model="edu.end_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('educations.' + index + '.end_month')" x-cloak
                                   x-text="fieldError('educations.' + index + '.end_month')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <x-button
                    type="button"
                    x-on:click="addEducation()"
                    :text="__('alumkit::education.add_education')"
                    xs
                    outline
                    block
                />
            </div>
            </div>

            {{-- Career Section --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.enter="event.target.tagName === 'TEXTAREA' || (event.preventDefault(), attemptStep(2))" @focusout="liveValidate()">
            <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('alumkit::career.career') }}
                </label>

                <template x-for="(career, index) in careers" :key="index">
                    <div class="rounded-lg border border-gray-200 p-4 space-y-4 dark:border-gray-700">
                        <div class="flex justify-end" x-show="careers.length > 0">
                            <x-button
                                type="button"
                                x-on:click="removeCareer(index)"
                                :text="__('alumkit::education.remove')"
                                xs
                                outline
                                color="red"
                            />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                x-bind:name="'careers[' + index + '][job_title]'"
                                x-model="career.job_title"
                                :label="__('alumkit::career.job_title')"
                                required
                            />
                            <p x-show="fieldError('careers.' + index + '.job_title')" x-cloak
                               x-text="fieldError('careers.' + index + '.job_title')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                x-bind:name="'careers[' + index + '][company]'"
                                x-model="career.company"
                                :label="__('alumkit::career.company')"
                                required
                            />
                            <p x-show="fieldError('careers.' + index + '.company')" x-cloak
                               x-text="fieldError('careers.' + index + '.company')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

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
                            <p x-show="fieldError('careers.' + index + '.employment_type')" x-cloak
                               x-text="fieldError('careers.' + index + '.employment_type')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                x-bind:name="'careers[' + index + '][industry]'"
                                x-model="career.industry"
                                :label="__('alumkit::career.industry')"
                            />
                            <p x-show="fieldError('careers.' + index + '.industry')" x-cloak
                               x-text="fieldError('careers.' + index + '.industry')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                x-bind:name="'careers[' + index + '][location]'"
                                x-model="career.location"
                                :label="__('alumkit::career.location')"
                            />
                            <p x-show="fieldError('careers.' + index + '.location')" x-cloak
                               x-text="fieldError('careers.' + index + '.location')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.start_year') }}</label>
                                <select x-bind:name="'careers[' + index + '][start_year]'" x-model="career.start_year" required class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(date('Y') + 5, 1970) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('careers.' + index + '.start_year')" x-cloak
                                   x-text="fieldError('careers.' + index + '.start_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.start_month') }}</label>
                                <select x-bind:name="'careers[' + index + '][start_month]'" x-model="career.start_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('careers.' + index + '.start_month')" x-cloak
                                   x-text="fieldError('careers.' + index + '.start_month')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                                <p x-show="fieldError('careers.' + index + '.end_year')" x-cloak
                                   x-text="fieldError('careers.' + index + '.end_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('alumkit::career.end_month') }}</label>
                                <select x-bind:name="'careers[' + index + '][end_month]'" x-model="career.end_month" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    <option value="">—</option>
                                    @foreach (range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fieldError('careers.' + index + '.end_month')" x-cloak
                                   x-text="fieldError('careers.' + index + '.end_month')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                            <p x-show="fieldError('careers.' + index + '.description')" x-cloak
                               x-text="fieldError('careers.' + index + '.description')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>
                    </div>
                </template>

                <x-button
                    type="button"
                    x-on:click="addCareer()"
                    :text="__('alumkit::career.add_career')"
                    xs
                    outline
                    block
                />
            </div>
            </div>

            {{-- Profile Details Section --}}
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @focusout="validateLocalNames($event)">
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('alumkit::profile.details') }}
                </label>

                <div>
                    <div x-data="{ photoPreview: null }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('alumkit::profile.photo') }}
                        </label>
                        <span role="button" tabindex="0"
                              class="flex h-24 w-32 cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-gold/50"
                              x-on:click="$refs.photoInput.click()"
                              x-on:keydown.enter.prevent="$refs.photoInput.click()"
                              x-on:keydown.space.prevent="$refs.photoInput.click()">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <span class="flex flex-col items-center gap-1.5 text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    <span>{{ __('alumkit::profile.choose_photo') }}</span>
                                </span>
                            </template>
                        </span>
                        <input type="file" name="photo" accept="image/*" hidden x-ref="photoInput"
                               x-on:change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </div>
                    <x-alumkit::input-error name="photo" />
                </div>

                @if (config('alumkit.local_names'))
                    <div class="space-y-4">
                        @foreach (config('alumkit.local_names') as $code => $langConfig)
                            <div>
                                <x-input
                                    type="text"
                                    name="local_names[{{ $code }}]"
                                    :value="old('local_names.' . $code)"
                                    :label="$langConfig['label']"
                                    :required="$langConfig['required'] ?? false"
                                />
                                <p x-show="fieldError('local_names.{{ $code }}')" x-cloak
                                   x-text="fieldError('local_names.{{ $code }}')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-input
                            type="date"
                            name="date_of_birth"
                            :value="old('date_of_birth')"
                            :label="__('alumkit::profile.date_of_birth')"
                        />
                        <x-alumkit::input-error name="date_of_birth" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-alumkit::select
                        name="gender"
                        :value="old('gender')"
                        :options="\Alumkit\Alumkit\Enums\Gender::options()"
                        :label="__('alumkit::profile.gender')"
                        placeholder="—"
                        required
                    />

                    <x-alumkit::select
                        name="blood_group"
                        :value="old('blood_group')"
                        :options="\Alumkit\Alumkit\Enums\BloodGroup::options()"
                        :label="__('alumkit::profile.blood_group')"
                        placeholder="—"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-input
                            type="text"
                            name="present_address"
                            :value="old('present_address')"
                            :label="__('alumkit::profile.present_address')"
                        />
                        <x-alumkit::input-error name="present_address" />
                    </div>

                    <div>
                        <x-input
                            type="text"
                            name="permanent_address"
                            :value="old('permanent_address')"
                            :label="__('alumkit::profile.permanent_address')"
                        />
                        <x-alumkit::input-error name="permanent_address" />
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::profile.social_links') }}
                    </label>

                    <div>
                        <x-input
                            type="url"
                            name="social_links[facebook]"
                            :value="old('social_links.facebook')"
                            :label="__('alumkit::profile.facebook')"
                        />
                        <x-alumkit::input-error name="social_links.facebook" />
                    </div>

                    <div>
                        <x-input
                            type="url"
                            name="social_links[linkedin]"
                            :value="old('social_links.linkedin')"
                            :label="__('alumkit::profile.linkedin')"
                        />
                        <x-alumkit::input-error name="social_links.linkedin" />
                    </div>

                    <div>
                        <x-input
                            type="url"
                            name="website"
                            :value="old('website')"
                            :label="__('alumkit::profile.website')"
                        />
                        <x-alumkit::input-error name="website" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::profile.emergency_contact') }}
                    </label>

                    <div class="mt-4 grid grid-cols-1 gap-4">
                        <div>
                            <x-input
                                type="text"
                                name="emergency_contact[name]"
                                :value="old('emergency_contact.name')"
                                :label="__('alumkit::profile.emergency_contact_name')"
                            />
                            <x-alumkit::input-error name="emergency_contact.name" />
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="emergency_contact[phone]"
                                :value="old('emergency_contact.phone')"
                                :label="__('alumkit::profile.emergency_contact_phone')"
                            />
                            <x-alumkit::input-error name="emergency_contact.phone" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input
                            type="text"
                            name="emergency_contact[relation]"
                            :value="old('emergency_contact.relation')"
                            :label="__('alumkit::profile.emergency_contact_relation')"
                        />
                        <x-alumkit::input-error name="emergency_contact.relation" />
                    </div>
                </div>
            </div>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2">
                <x-button
                    type="button"
                    x-show="step > 1"
                    x-cloak
                    x-on:click="step--"
                    :text="__('alumkit::auth.back')"
                    outline
                />
                <div class="flex justify-end">
                    <x-button
                        type="button"
                        x-show="step < 3"
                        x-cloak
                        x-on:click="attemptStep(step)"
                        :text="__('alumkit::auth.next')"
                    />
                    <x-button
                        type="submit"
                        x-show="step === 3"
                        x-cloak
                        :text="$isAdmin ? __('alumkit::auth.submit') : __('alumkit::auth.submit_for_approval')"
                    />
                </div>
            </div>
        </form>

        @slot('footer')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button type="submit" block outline :text="__('alumkit::auth.logout')" />
            </form>
        @endslot
    </x-alumkit::form-wrapper>
@endsection
