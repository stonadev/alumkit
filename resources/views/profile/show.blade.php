@php
    use Illuminate\Support\Facades\Auth;
    use Laravel\Fortify\Features;
@endphp

@extends('alumkit::layouts.app')

@section('content')
    <div class="space-y-6">
        <x-card>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('alumkit::auth.profile') }}
                </h1>
            </div>
        </x-card>

        @if (Features::enabled(Features::updateProfileInformation()))
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('alumkit::auth.update_info') }}
                </h2>

                <x-errors />

                @if (session('status') === 'profile-information-updated')
                    <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                        {{ __('alumkit::auth.profile_updated') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <x-input
                        type="email"
                        name="email"
                        :value="old('email', Auth::user()->email)"
                        :label="__('alumkit::auth.email')"
                        required
                    />

                    <x-button type="submit" :text="__('alumkit::auth.save')" />
                </form>
            </x-card>
        @endif

        @if (Features::enabled(Features::updatePasswords()))
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('alumkit::auth.update_password') }}
                </h2>

                <x-errors />

                @if (session('status') === 'password-updated')
                    <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                        {{ __('alumkit::auth.password_updated') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('user-password.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <x-password
                        name="current_password"
                        :label="__('alumkit::auth.current_password')"
                        required
                        autocomplete="current-password"
                    />

                    <x-password
                        name="password"
                        :label="__('alumkit::auth.new_password')"
                        required
                    />

                    <x-password
                        name="password_confirmation"
                        :label="__('alumkit::auth.confirm_password')"
                        required
                    />

                    <x-button type="submit" :text="__('alumkit::auth.save')" />
                </form>
            </x-card>
        @endif

        @if (Features::enabled(Features::twoFactorAuthentication()))
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('alumkit::auth.two_factor_auth') }}
                </h2>

                @if (session('status') === 'two-factor-authentication-enabled')
                    <div class="mt-4 text-sm text-green-600 dark:text-green-400">
                        {{ __('alumkit::auth.two_factor_enabled') }}
                    </div>

                    @if (session('confirmation') === 'required')
                        <div class="mt-4 space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('alumkit::auth.two_factor_scan_qr') }}
                            </p>

                            <div class="flex justify-center">
                                {!! Auth::user()->twoFactorQrCodeSvg() !!}
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('alumkit::auth.two_factor_setup_key') }}
                                <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                    {{ decrypt(Auth::user()->two_factor_secret) }}
                                </code>
                            </p>

                            <form method="POST" action="{{ route('two-factor.confirm') }}">
                                @csrf
                                <div class="space-y-4">
                                    <x-input
                                        type="text"
                                        name="code"
                                        :label="__('alumkit::auth.two_factor_code')"
                                        inputmode="numeric"
                                        required
                                    />
                                    <x-button type="submit" :text="__('alumkit::auth.confirm')" />
                                </div>
                            </form>
                        </div>
                    @endif

                    @if (session('recoveryCodes'))
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('alumkit::auth.two_factor_recovery_codes') }}
                            </p>
                            <div class="mt-2 bg-gray-100 dark:bg-gray-800 rounded-md p-4">
                                @foreach (session('recoveryCodes') as $code)
                                    <code class="block text-sm">{{ $code }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                @if (session('status') === 'two-factor-authentication-confirmed')
                    <div class="mt-4 text-sm text-green-600 dark:text-green-400">
                        {{ __('alumkit::auth.two_factor_confirmed') }}
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" color="red" :text="__('alumkit::auth.disable_2fa')" />
                        </form>

                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                            @csrf
                            <x-button type="submit" outline :text="__('alumkit::auth.regenerate_recovery_codes')" />
                        </form>
                    </div>
                @endif

                @if (
                    ! session('status') ||
                    (session('status') !== 'two-factor-authentication-enabled' &&
                     session('status') !== 'two-factor-authentication-confirmed'))
                    <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
                        @csrf
                        <x-button type="submit" :text="__('alumkit::auth.enable_2fa')" />
                    </form>
                @endif
            </x-card>
        @endif

        <div class="text-center">
            <a href="{{ route('alumkit.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                {{ __('alumkit::auth.back_to_dashboard') }}
            </a>
        </div>
    </div>
@endsection
