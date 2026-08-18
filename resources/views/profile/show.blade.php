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

        <div
            x-data="{
                tab: 'profile',
                tabs: ['profile', 'education', 'career', 'security'],
                syncTab() { this.tab = this.tabs.includes((location.hash || '#profile').slice(1)) ? (location.hash || '#profile').slice(1) : 'profile'; },
                moveTab(direction) {
                    const index = this.tabs.indexOf(this.tab);
                    document.getElementById('tab-' + this.tabs[(index + direction + this.tabs.length) % this.tabs.length])?.focus();
                },
            }"
            x-init="syncTab()"
            @hashchange.window="syncTab()"
        >
            <nav role="tablist" aria-label="{{ __('alumkit::profile.details') }}" @keydown.arrow-right.prevent="moveTab(1)" @keydown.arrow-left.prevent="moveTab(-1)" class="mb-6 flex gap-1 border-b border-outline-variant/60">
                <a href="#profile" id="tab-profile" role="tab" aria-controls="profile" :aria-selected="tab === 'profile'" :tabindex="tab === 'profile' ? 0 : -1"
                   :class="tab === 'profile' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">{{ __('alumkit::auth.profile') }}</a>

                <a href="#education" id="tab-education" role="tab" aria-controls="education" :aria-selected="tab === 'education'" :tabindex="tab === 'education' ? 0 : -1"
                   :class="tab === 'education' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">{{ __('alumkit::education.education') }}</a>

                <a href="#career" id="tab-career" role="tab" aria-controls="career" :aria-selected="tab === 'career'" :tabindex="tab === 'career' ? 0 : -1"
                   :class="tab === 'career' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">{{ __('alumkit::career.career') }}</a>

                <a href="#security" id="tab-security" role="tab" aria-controls="security" :aria-selected="tab === 'security'" :tabindex="tab === 'security' ? 0 : -1"
                   :class="tab === 'security' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">{{ __('alumkit::profile.security') }}</a>
            </nav>

            <section id="profile" role="tabpanel" aria-labelledby="tab-profile" x-show="tab === 'profile'" x-cloak>
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
            </section>

            <section id="education" role="tabpanel" aria-labelledby="tab-education" x-show="tab === 'education'" x-cloak>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-navy">
                        {{ __('alumkit::education.educations') }}
                    </h2>
                    <a href="{{ route('alumkit.profile.educations.create') }}">
                        <x-button :text="__('alumkit::education.add_education')" />
                    </a>
                </div>

                @php $educations = Auth::user()->educations()->orderByDesc('start_year')->get(); @endphp

                @forelse ($educations as $education)
                    <div class="mt-4">
                        <x-card>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    @if ($education->start_year)
                                        <p class="label-caps text-gold">
                                            {{ $education->start_year }} — {{ $education->end_year ?? __('alumkit::career.present') }}
                                        </p>
                                    @endif
                                    <h3 class="mt-1 font-serif text-lg font-semibold text-navy">{{ $education->institution }}</h3>
                                    <p class="mt-0.5 text-sm text-on-surface-variant">
                                        {{ $education->level }}{{ $education->subject ? ' · '.$education->subject : '' }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-3 text-sm">
                                    <a href="{{ route('alumkit.profile.educations.edit', $education) }}" class="text-navy hover:text-gold">{{ __('alumkit::dashboard.edit') }}</a>
                                    <form method="POST" action="{{ route('alumkit.profile.educations.destroy', $education) }}" class="inline" data-confirm="{{ __('alumkit::dashboard.confirm_delete') }}" onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('alumkit::dashboard.delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </x-card>
                    </div>
                @empty
                    <div class="mt-4">
                        <x-card>
                            <p class="text-sm text-on-surface-variant">{{ __('alumkit::education.no_educations') }}</p>
                            <a href="{{ route('alumkit.profile.educations.create') }}" class="mt-3 inline-block">
                                <x-button :text="__('alumkit::education.add_education')" outline />
                            </a>
                        </x-card>
                    </div>
                @endforelse
            </section>

            <section id="career" role="tabpanel" aria-labelledby="tab-career" x-show="tab === 'career'" x-cloak>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-navy">
                        {{ __('alumkit::career.careers') }}
                    </h2>
                    <a href="{{ route('alumkit.profile.careers.create') }}">
                        <x-button :text="__('alumkit::career.add_career')" />
                    </a>
                </div>

                @php $careers = Auth::user()->careers()->orderByDesc('start_year')->get(); @endphp

                @forelse ($careers as $career)
                    <div class="mt-4">
                        <x-card>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="label-caps text-gold">
                                        {{ $career->start_year }} — {{ $career->is_current ? __('alumkit::career.present') : ($career->end_year ?? '—') }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <h3 class="font-serif text-lg font-semibold text-navy">{{ $career->job_title }}</h3>
                                        <span class="rounded bg-surface-container px-2 py-0.5 text-xs font-medium text-navy">{{ config("alumkit.career.employment_types.{$career->employment_type->value}", $career->employment_type->value) }}</span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-on-surface-variant">{{ $career->company }}</p>
                                    @if ($career->industry || $career->location)
                                        <p class="mt-0.5 text-sm text-on-surface-variant">{{ $career->industry }} · {{ $career->location }}</p>
                                    @endif
                                    @if ($career->description)
                                        <p class="mt-2 text-sm text-on-surface-variant">{{ $career->description }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-3 text-sm">
                                    <a href="{{ route('alumkit.profile.careers.edit', $career) }}" class="text-navy hover:text-gold">{{ __('alumkit::dashboard.edit') }}</a>
                                    <form method="POST" action="{{ route('alumkit.profile.careers.destroy', $career) }}" class="inline" data-confirm="{{ __('alumkit::dashboard.confirm_delete') }}" onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('alumkit::dashboard.delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </x-card>
                    </div>
                @empty
                    <div class="mt-4">
                        <x-card>
                            <p class="text-sm text-on-surface-variant">{{ __('alumkit::career.no_careers') }}</p>
                            <a href="{{ route('alumkit.profile.careers.create') }}" class="mt-3 inline-block">
                                <x-button :text="__('alumkit::career.add_career')" outline />
                            </a>
                        </x-card>
                    </div>
                @endforelse
            </section>

            <section id="security" role="tabpanel" aria-labelledby="tab-security" x-show="tab === 'security'" x-cloak>
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
            </section>
        </div>

        <div class="text-center">
            <a href="{{ route('alumkit.dashboard') }}" class="text-sm text-navy hover:text-gold">
                {{ __('alumkit::auth.back_to_dashboard') }}
            </a>
        </div>
    </div>
@endsection
