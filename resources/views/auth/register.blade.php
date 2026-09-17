@extends('alumkit::layouts.app')

@section('content')
    <x-alumkit::form-wrapper :title="__('alumkit::auth.register')" :show-errors="false">
        <form method="POST" action="{{ route('register') }}" class="space-y-4"
              x-data="alumkitForm({
                  name: { required: true, requiredMsg: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.name')])) }} },
                  email: { required: true, requiredMsg: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.email')])) }}, email: true, emailMsg: {{ Js::from(__('validation.email', ['attribute' => __('alumkit::auth.email')])) }} },
                  phone: { required: true, requiredMsg: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.phone')])) }} },
                  password: { required: true, requiredMsg: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.password')])) }}, min: 8, minMsg: {{ Js::from(__('validation.min.string', ['attribute' => __('alumkit::auth.password'), 'min' => 8])) }} },
                  password_confirmation: { required: true, requiredMsg: {{ Js::from(__('validation.required', ['attribute' => __('alumkit::auth.confirm_password')])) }}, confirmed: 'password', confirmedMsg: {{ Js::from(__('validation.confirmed', ['attribute' => __('alumkit::auth.confirm_password')])) }} },
              }, {{ Js::from($errors->getMessages()) }})"
              @focusout="validateField($event.target.name, $event.target.value)">
            @csrf

            <div>
                <x-input
                    type="text"
                    name="name"
                    :value="old('name')"
                    :label="__('alumkit::auth.name')"
                    required
                />
            </div>

            <div>
                <x-input
                    type="email"
                    name="email"
                    :value="old('email')"
                    :label="__('alumkit::auth.email')"
                    required
                />
            </div>

            <div>
                <x-input
                    type="tel"
                    name="phone"
                    :value="old('phone')"
                    :label="__('alumkit::auth.phone')"
                    required
                />
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

            <x-button type="submit" block :text="__('alumkit::auth.register')" />
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
