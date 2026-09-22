@extends('alumkit::layouts.app')

@section('content')
    @php
        $defaultEducation = ['level' => '', 'institution' => '', 'student_id' => '', 'subject' => '', 'start_year' => '', 'start_month' => '', 'is_current' => false, 'end_year' => '', 'end_month' => ''];
        $oldEducations = array_map(
            fn (array $e): array => array_merge($defaultEducation, $e),
            old('educations', []) ?: [$defaultEducation],
        );

        $initialStep = 1;
        if ($errors->isNotEmpty()) {
            $keys = array_keys($errors->getMessages());
            if (collect($keys)->contains(fn (string $k): bool => str_starts_with($k, 'educations'))) {
                $initialStep = 1;
            } else {
                $initialStep = 2;
            }
        }
    @endphp

    <x-alumkit::form-wrapper :title="__('alumkit::auth.register')" :show-errors="false">
        <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{
            step: {{ $initialStep }},
            educations: {{ Js::from($oldEducations) }},
            errors: {{ Js::from($errors->getMessages()) }},
            eager: {},
            toKey(name) { return String(name || '').replace(/\[([^\]]+)\]/g, '.$1'); },
            fieldError(key) { return (this.errors[this.toKey(key)] || [])[0] || null; },
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
                    const nameEl = this.$refs.form.querySelector('[name=name]');
                    const emailEl = this.$refs.form.querySelector('[name=email]');
                    const phoneEl = this.$refs.form.querySelector('[name=phone]');
                    const pwEl = this.$refs.form.querySelector('[name=password]');
                    const pwConfEl = this.$refs.form.querySelector('[name=password_confirmation]');
                    if (nameEl && !nameEl.value.trim()) { this.errors['name'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.name')])) }}]; valid = false; } else if (nameEl && !/^[A-Za-z\s]+$/.test(nameEl.value)) { this.errors['name'] = [{{ Js::from(__('alumkit::validation.name_latin_only')) }}]; valid = false; } else { delete this.errors['name']; }
                    if (emailEl && !emailEl.value.trim()) { this.errors['email'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.email')])) }}]; valid = false; } else if (emailEl && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) { this.errors['email'] = [{{ Js::from(__('validation.email', ['attribute' => __('alumkit::auth.email')])) }}]; valid = false; } else { delete this.errors['email']; }
                    if (phoneEl && !phoneEl.value.trim()) { this.errors['phone'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.phone')])) }}]; valid = false; } else { delete this.errors['phone']; }
                    if (pwEl && !pwEl.value) { this.errors['password'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.password')])) }}]; valid = false; } else if (pwEl && pwEl.value.length < 8) { this.errors['password'] = [{{ Js::from(__('validation.min.string', ['attribute' => __('alumkit::auth.password'), 'min' => 8])) }}]; valid = false; } else { delete this.errors['password']; }
                    if (pwConfEl && !pwConfEl.value) { this.errors['password_confirmation'] = [{{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.confirm_password')])) }}]; valid = false; } else if (pwConfEl && pwEl && pwConfEl.value !== pwEl.value) { this.errors['password_confirmation'] = [{{ Js::from(__('validation.confirmed', ['attribute' => __('alumkit::auth.confirm_password')])) }}]; valid = false; } else { delete this.errors['password_confirmation']; }
                }
                return valid;
            },
            clearStepErrors(s) {
                const prefix = s === 1 ? 'educations.' : null;
                Object.keys(this.errors).forEach((k) => { if (prefix && k.startsWith(prefix)) delete this.errors[k]; });
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
            removeEducation(i) { this.educations.splice(i, 1); }
        }" x-ref="form">
            @csrf

            <ol aria-label="{{ __('alumkit::auth.register') }}" class="mx-auto mb-6 flex w-full max-w-xs items-center text-xs">
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
                {{-- Node 2: Account Details --}}
                <li class="flex items-center" :aria-current="step === 2 ? 'step' : null">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs font-semibold"
                          :class="step === 2 ? 'border-navy text-navy' : 'border-gray-300 text-gray-400'">
                        2
                    </span>
                    <span class="ml-2 hidden font-medium sm:block" :class="step >= 2 ? 'text-navy' : 'text-gray-400'">{{ __('alumkit::auth.account_details') }}</span>
                </li>
            </ol>

            <p class="mb-4 text-center text-xs text-gray-500 sm:hidden">{{ __('alumkit::auth.step') }} <span x-text="step" class="font-semibold text-navy"></span> {{ __('alumkit::auth.of') }} 2</p>

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
                                <x-alumkit::year-select
                                    x-bind:name="'educations[' + index + '][start_year]'"
                                    x-model="edu.start_year"
                                    :label="__('alumkit::education.start_year')"
                                    :show-error="false"
                                    required
                                />
                                <p x-show="fieldError('educations.' + index + '.start_year')" x-cloak
                                   x-text="fieldError('educations.' + index + '.start_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <x-alumkit::month-select
                                    x-bind:name="'educations[' + index + '][start_month]'"
                                    x-model="edu.start_month"
                                    :label="__('alumkit::education.start_month')"
                                    :show-error="false"
                                />
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
                                <x-alumkit::year-select
                                    x-bind:name="'educations[' + index + '][end_year]'"
                                    x-model="edu.end_year"
                                    :label="__('alumkit::education.end_year')"
                                    :show-error="false"
                                />
                                <p x-show="fieldError('educations.' + index + '.end_year')" x-cloak
                                   x-text="fieldError('educations.' + index + '.end_year')"
                                   class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                            </div>
                            <div>
                                <x-alumkit::month-select
                                    x-bind:name="'educations[' + index + '][end_month]'"
                                    x-model="edu.end_month"
                                    :label="__('alumkit::education.end_month')"
                                    :show-error="false"
                                />
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

            {{-- Account Details Section --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.enter="event.target.tagName === 'TEXTAREA' || (event.preventDefault(), attemptStep(2))" @focusout="liveValidate()">
            <div class="space-y-4">
                <div>
                    <x-input
                        type="text"
                        name="name"
                        :value="old('name')"
                        :label="__('alumkit::auth.name')"
                        required
                        invalidate
                    />
                    <p x-show="fieldError('name')" x-cloak x-text="fieldError('name')"
                       class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                </div>

                <div>
                    <x-input
                        type="email"
                        name="email"
                        :value="old('email')"
                        :label="__('alumkit::auth.email')"
                        required
                        invalidate
                    />
                    <p x-show="fieldError('email')" x-cloak x-text="fieldError('email')"
                       class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                </div>

                <div>
                    <x-input
                        type="tel"
                        name="phone"
                        :value="old('phone')"
                        :label="__('alumkit::auth.phone')"
                        required
                        invalidate
                    />
                    <p x-show="fieldError('phone')" x-cloak x-text="fieldError('phone')"
                       class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                </div>

                <div>
                    <x-alumkit::password
                        name="password"
                        :label="__('alumkit::auth.password')"
                        required
                        :show-error="false"
                    />
                    <p x-show="fieldError('password')" x-cloak x-text="fieldError('password')"
                       class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
                </div>

                <div>
                    <x-alumkit::password
                        name="password_confirmation"
                        :label="__('alumkit::auth.confirm_password')"
                        required
                        :show-error="false"
                    />
                    <p x-show="fieldError('password_confirmation')" x-cloak x-text="fieldError('password_confirmation')"
                       class="mt-1.5 text-sm font-medium text-error" role="alert"></p>
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
                        :text="__('alumkit::auth.register')"
                    />
                </div>
            </div>
        </form>

        @slot('footer')
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('alumkit::auth.already_registered') }}
            </span>
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                {{ __('alumkit::auth.sign_in') }}
            </a>
        @endslot
    </x-alumkit::form-wrapper>
@endsection
