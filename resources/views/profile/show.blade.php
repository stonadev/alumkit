@php
    use Illuminate\Support\Facades\Auth;
    use Laravel\Fortify\Features;
@endphp

@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="space-y-6">
        <x-card>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-navy">
                    {{ __('alumkit::auth.profile') }}
                </h1>
            </div>
        </x-card>

        @if (Features::enabled(Features::updateProfileInformation()))
            <x-card>
                <h2 class="text-lg font-semibold text-navy">
                    {{ __('alumkit::auth.update_info') }}
                </h2>

                <x-errors />

                @if (session('status') === 'profile-information-updated')
                    <div class="mt-2 text-sm text-green-600">
                        {{ __('alumkit::auth.profile_updated') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <x-input
                        type="text"
                        name="name"
                        :value="old('name', Auth::user()->name)"
                        :label="__('alumkit::auth.name')"
                        required
                    />

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

        <x-card>
            <h2 class="text-lg font-semibold text-navy">
                {{ __('alumkit::profile.details') }}
            </h2>

            <x-errors />

            @if (session('status') === 'profile-details-updated')
                <div class="mt-2 text-sm text-green-600">
                    {{ __('alumkit::profile.updated') }}
                </div>
            @endif

            <form method="POST" action="{{ route('alumkit.profile.details.update') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                @if (Auth::user()->profile->photoUrl())
                    <img src="{{ Auth::user()->profile->photoUrl() }}" class="h-24 w-24 rounded-lg object-cover" alt="{{ __('alumkit::profile.photo') }}">
                @endif

                <x-input type="file" name="photo" :label="__('alumkit::profile.photo')" />

                <div class="grid grid-cols-2 gap-4">
                    <x-input
                        type="date"
                        name="date_of_birth"
                        :value="old('date_of_birth', Auth::user()->profile->date_of_birth?->format('Y-m-d'))"
                        :label="__('alumkit::profile.date_of_birth')"
                    />

                    <x-input
                        type="url"
                        name="website"
                        :value="old('website', Auth::user()->profile->website)"
                        :label="__('alumkit::profile.website')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-alumkit::select
                        name="gender"
                        :options="\Alumkit\Alumkit\Enums\Gender::options()"
                        :value="old('gender', Auth::user()->profile->gender?->value)"
                        :label="__('alumkit::profile.gender')"
                    />

                    <x-alumkit::select
                        name="blood_group"
                        :options="\Alumkit\Alumkit\Enums\BloodGroup::options()"
                        :value="old('blood_group', Auth::user()->profile->blood_group?->value)"
                        :label="__('alumkit::profile.blood_group')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-input
                        type="text"
                        name="present_address"
                        :value="old('present_address', Auth::user()->profile->present_address)"
                        :label="__('alumkit::profile.present_address')"
                    />

                    <x-input
                        type="text"
                        name="permanent_address"
                        :value="old('permanent_address', Auth::user()->profile->permanent_address)"
                        :label="__('alumkit::profile.permanent_address')"
                    />
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::profile.social_links') }}
                    </label>

                    <x-input
                        type="url"
                        name="social_links[facebook]"
                        :value="old('social_links.facebook', Auth::user()->profile->social_links['facebook'] ?? '')"
                        :label="__('alumkit::profile.facebook')"
                    />

                    <x-input
                        type="url"
                        name="social_links[linkedin]"
                        :value="old('social_links.linkedin', Auth::user()->profile->social_links['linkedin'] ?? '')"
                        :label="__('alumkit::profile.linkedin')"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('alumkit::profile.emergency_contact') }}
                    </label>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <x-input
                            type="text"
                            name="emergency_contact[name]"
                            :value="old('emergency_contact.name', Auth::user()->profile->emergency_contact['name'] ?? '')"
                            :label="__('alumkit::profile.emergency_contact_name')"
                        />

                        <x-input
                            type="text"
                            name="emergency_contact[phone]"
                            :value="old('emergency_contact.phone', Auth::user()->profile->emergency_contact['phone'] ?? '')"
                            :label="__('alumkit::profile.emergency_contact_phone')"
                        />
                    </div>

                    <div class="mt-4">
                        <x-input
                            type="text"
                            name="emergency_contact[relation]"
                            :value="old('emergency_contact.relation', Auth::user()->profile->emergency_contact['relation'] ?? '')"
                            :label="__('alumkit::profile.emergency_contact_relation')"
                        />
                    </div>
                </div>

                <x-button type="submit" :text="__('alumkit::profile.save')" />
            </form>
        </x-card>

        @if (Features::enabled(Features::updatePasswords()))
            <x-card>
                <h2 class="text-lg font-semibold text-navy">
                    {{ __('alumkit::auth.update_password') }}
                </h2>

                <x-errors />

                @if (session('status') === 'password-updated')
                    <div class="mt-2 text-sm text-green-600">
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
                <h2 class="text-lg font-semibold text-navy">
                    {{ __('alumkit::auth.two_factor_auth') }}
                </h2>

                @if (session('status') === 'two-factor-authentication-enabled')
                    <div class="mt-4 text-sm text-green-600">
                        {{ __('alumkit::auth.two_factor_enabled') }}
                    </div>

                    @if (session('confirmation') === 'required')
                        <div class="mt-4 space-y-4">
                            <p class="text-sm text-gray-600">
                                {{ __('alumkit::auth.two_factor_scan_qr') }}
                            </p>

                            <div class="flex justify-center">
                                {!! Auth::user()->twoFactorQrCodeSvg() !!}
                            </div>

                            <p class="text-sm text-gray-600">
                                {{ __('alumkit::auth.two_factor_setup_key') }}
                                <code class="bg-gray-100 px-2 py-1 rounded">
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
                            <p class="text-sm text-gray-600">
                                {{ __('alumkit::auth.two_factor_recovery_codes') }}
                            </p>
                            <div class="mt-2 bg-gray-100 rounded-md p-4">
                                @foreach (session('recoveryCodes') as $code)
                                    <code class="block text-sm">{{ $code }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                @if (session('status') === 'two-factor-authentication-confirmed')
                    <div class="mt-4 text-sm text-green-600">
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
            <a href="{{ route('alumkit.dashboard') }}" class="text-sm text-navy hover:text-gold">
                {{ __('alumkit::auth.back_to_dashboard') }}
            </a>
        </div>
    </div>
@endsection
