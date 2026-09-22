@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.complete_profile')" :show-errors="false">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('alumkit::auth.complete_profile_text') }}
        </p>

        @php
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
                if (collect($keys)->contains(fn (string $k): bool => str_starts_with($k, 'careers'))) {
                    $initialStep = 1;
                } else {
                    $initialStep = 2;
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
            careers: {{ Js::from($oldCareers) }},
            errors: {{ Js::from($errors->getMessages()) }},
            eager: {},
            localNameRules: {{ Js::from($localNameRules) }},
            toKey(name) { return String(name || '').replace(/\[([^\]]+)\]/g, '.$1'); },
            fieldError(key) { return (this.errors[this.toKey(key)] || [])[0] || null; },
            validateBlurField(e) {
                const key = this.toKey(e.target.name);
                const value = e.target.value;
                const r = this.localNameRules[key];
                if (r) {
                    if (r.required && !String(value).trim()) { this.errors[key] = [r.requiredMsg]; }
                    else { delete this.errors[key]; }
                    return;
                }
                const requiredFields = {
                    present_address: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::profile.present_address')])) }},
                    permanent_address: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::profile.permanent_address')])) }},
                };
                if (key in requiredFields) {
                    if (!String(value).trim()) { this.errors[key] = [requiredFields[key]]; }
                    else { delete this.errors[key]; }
                }
            },
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
                    this.careers.forEach((c, i) => {
                        const p = 'careers.' + i + '.';
                        if (!c.job_title) { this.errors[p + 'job_title'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.job_title')])) }}]; valid = false; }
                        if (!c.company) { this.errors[p + 'company'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.company')])) }}]; valid = false; }
                        if (!c.employment_type) { this.errors[p + 'employment_type'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.employment_type')])) }}]; valid = false; }
                        if (!c.start_year) { this.errors[p + 'start_year'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::career.start_year')])) }}]; valid = false; }
                    });
                }
                if (s === 2) {
                    const form = this.$refs.form;
                    const required = [
                        ['present_address', {{ Js::from(__('validation.required', ['attribute' => __('alumkit::profile.present_address')])) }}],
                        ['permanent_address', {{ Js::from(__('validation.required', ['attribute' => __('alumkit::profile.permanent_address')])) }}],
                    ];
                    required.forEach(([name, msg]) => {
                        const v = form.elements[name]?.value ?? '';
                        if (!v.trim()) { this.errors[name] = [msg]; valid = false; }
                    });
                }
                return valid;
            },
            clearStepErrors(s) {
                const prefixes = s === 1 ? ['careers.'] : ['present_address', 'permanent_address'];
                Object.keys(this.errors).forEach((k) => {
                    if (s === 1) {
                        if (k.startsWith('careers.')) delete this.errors[k];
                    } else {
                        if (prefixes.includes(k)) delete this.errors[k];
                    }
                });
            },
            attemptStep(s) {
                this.eager[s] = true;
                if (this.validateStep(s)) { this.step++; }
            },
            liveValidate() {
                if (this.eager[this.step]) { this.validateStep(this.step); }
            },
            addCareer() {
                this.careers.push({ job_title: '', company: '', employment_type: '{{ array_key_first($employmentTypes) }}', industry: '', location: '', start_year: '', start_month: '', is_current: false, end_year: '', end_month: '', description: '' });
            },
            removeCareer(i) { this.careers.splice(i, 1); }
        }" x-ref="form">
            @csrf

            <ol aria-label="{{ __('alumkit::auth.complete_profile') }}" class="mx-auto mb-6 flex w-full max-w-xs items-center text-xs">
                {{-- Node 1: Career --}}
                <li class="flex flex-1 items-center" :aria-current="step === 1 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step > 1 ? 'border-navy bg-navy text-white' : (step === 1 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400')">
                        <span x-show="step > 1" x-cloak>&#10003;</span>
                        <span x-show="step <= 1" x-cloak>1</span>
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 1 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::career.career') }}</span>
                    <span class="mx-2 h-px flex-1" :class="step > 1 ? 'bg-navy' : 'bg-gray-300'"></span>
                </li>
                {{-- Node 2: Profile Details --}}
                <li class="flex items-center" :aria-current="step === 2 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step === 2 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400'">
                        2
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 2 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::profile.details') }}</span>
                </li>
            </ol>

            <p class="mb-4 text-center text-xs text-gray-500 sm:hidden">{{ __('alumkit::auth.step') }} <span x-text="step" class="font-semibold text-navy"></span> {{ __('alumkit::auth.of') }} 2</p>

            {{-- Career Section --}}
            <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.enter="event.target.tagName === 'TEXTAREA' || (event.preventDefault(), attemptStep(1))" @focusout="liveValidate()">
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
                                <x-alumkit::year-select
                                    x-bind:name="'careers[' + index + '][start_year]'"
                                    x-model="career.start_year"
                                    :label="__('alumkit::career.start_year')"
                                    :show-error="false"
                                    required
                                />
                                <p x-show="fieldError('careers.' + index + '.start_year')" x-cloak
                                   x-text="fieldError('careers.' + index + '.start_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <x-alumkit::month-select
                                    x-bind:name="'careers[' + index + '][start_month]'"
                                    x-model="career.start_month"
                                    :label="__('alumkit::career.start_month')"
                                    :show-error="false"
                                />
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
                                <x-alumkit::year-select
                                    x-bind:name="'careers[' + index + '][end_year]'"
                                    x-model="career.end_year"
                                    :label="__('alumkit::career.end_year')"
                                    :show-error="false"
                                />
                                <p x-show="fieldError('careers.' + index + '.end_year')" x-cloak
                                   x-text="fieldError('careers.' + index + '.end_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <x-alumkit::month-select
                                    x-bind:name="'careers[' + index + '][end_month]'"
                                    x-model="career.end_month"
                                    :label="__('alumkit::career.end_month')"
                                    :show-error="false"
                                />
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
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.enter="event.target.tagName === 'TEXTAREA' || (event.preventDefault(), attemptStep(2))" @focusout="validateBlurField($event)">
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('alumkit::profile.details') }}
                </label>

                <x-alumkit::photo-cropper name="photo"
                    :choose-label="__('alumkit::profile.choose_photo')"
                    box-class="h-32 w-32"
                    icon-class="h-6 w-6"
                    text-class="text-xs">
                    <x-slot:label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('alumkit::profile.photo') }}
                        </label>
                    </x-slot:label>
                </x-alumkit::photo-cropper>

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
                        <p x-show="fieldError('date_of_birth')" x-cloak x-text="fieldError('date_of_birth')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                            required
                        />
                        <p x-show="fieldError('present_address')" x-cloak x-text="fieldError('present_address')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                    </div>

                    <div>
                        <x-input
                            type="text"
                            name="permanent_address"
                            :value="old('permanent_address')"
                            :label="__('alumkit::profile.permanent_address')"
                            required
                        />
                        <p x-show="fieldError('permanent_address')" x-cloak x-text="fieldError('permanent_address')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                        <p x-show="fieldError('social_links.facebook')" x-cloak x-text="fieldError('social_links.facebook')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                    </div>

                    <div>
                        <x-input
                            type="url"
                            name="social_links[linkedin]"
                            :value="old('social_links.linkedin')"
                            :label="__('alumkit::profile.linkedin')"
                        />
                        <p x-show="fieldError('social_links.linkedin')" x-cloak x-text="fieldError('social_links.linkedin')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                    </div>

                    <div>
                        <x-input
                            type="url"
                            name="website"
                            :value="old('website')"
                            :label="__('alumkit::profile.website')"
                        />
                        <p x-show="fieldError('website')" x-cloak x-text="fieldError('website')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                            <p x-show="fieldError('emergency_contact.name')" x-cloak x-text="fieldError('emergency_contact.name')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>

                        <div>
                            <x-input
                                type="text"
                                name="emergency_contact[phone]"
                                :value="old('emergency_contact.phone')"
                                :label="__('alumkit::profile.emergency_contact_phone')"
                            />
                            <p x-show="fieldError('emergency_contact.phone')" x-cloak x-text="fieldError('emergency_contact.phone')"
                               class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input
                            type="text"
                            name="emergency_contact[relation]"
                            :value="old('emergency_contact.relation')"
                            :label="__('alumkit::profile.emergency_contact_relation')"
                        />
                        <p x-show="fieldError('emergency_contact.relation')" x-cloak x-text="fieldError('emergency_contact.relation')"
                           class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                        x-show="step < 2"
                        x-cloak
                        x-on:click="attemptStep(step)"
                        :text="__('alumkit::auth.next')"
                    />
                    <x-button
                        type="submit"
                        x-show="step === 2"
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
