@extends('alumkit::layouts.dashboard')

@section('content')
    @php
        $profile = $user->profile;
        $educations = collect($profile?->educations ?? [])->sortByDesc('start_year');
        $careers = collect($profile?->careers ?? [])->sortByDesc('start_year');
        $currentCareer = $careers->firstWhere('is_current', true) ?? $careers->first();
        $currentState = \Alumkit\Alumkit\Enums\UserState::from($user->state);
        $transitions = $currentState->transitions();
        $socials = collect($profile?->social_links ?? [])->filter();
        $emergency = $profile?->emergency_contact ?? [];
    @endphp

    <a href="{{ route('alumkit.users.index') }}" class="mb-6 inline-block text-sm text-navy hover:text-gold">
        {{ __('alumkit::dashboard.back_to_users') }}
    </a>

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Identity rail --}}
        <aside class="space-y-6 lg:order-2 lg:col-span-4">
            <div class="card p-6">
                <div class="flex items-center gap-5">
                    @if ($profile?->photoUrl())
                        <img src="{{ $profile->photoUrl() }}" alt="{{ $user->name }}" class="h-28 w-24 shrink-0 rounded-lg object-cover">
                    @else
                        <div class="flex h-28 w-24 shrink-0 items-center justify-center rounded-lg bg-surface-container font-serif text-2xl font-semibold text-navy">
                            {{ \Illuminate\Support\Str::initials($user->name) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        @if ($currentCareer)
                            <p class="label-caps text-gold">{{ $currentCareer->job_title }}</p>
                        @endif
                        <h1 class="mt-1 font-serif text-2xl font-semibold leading-tight text-navy">{{ $user->name }}</h1>
                        <p class="mt-1 truncate text-sm text-on-surface-variant">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    @include('alumkit::users.partials.state-badge', ['state' => $user->state])
                    @foreach ($user->roles as $role)
                        <span class="rounded bg-surface-container px-2 py-0.5 text-xs font-medium text-navy">{{ $role->name }}</span>
                    @endforeach
                </div>

                <dl class="mt-6 space-y-3 border-t border-outline-variant/60 pt-5 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="shrink-0 text-on-surface-variant">{{ __('alumkit::dashboard.member_since_label') }}</dt>
                        <dd class="text-right text-navy">{{ $user->created_at?->format('M Y') }}</dd>
                    </div>
                </dl>

                @if ($profile)
                    <div class="mt-6 border-t border-outline-variant/60 pt-5">
                        <p class="label-caps text-gold">{{ __('alumkit::profile.details') }}</p>
                        <dl class="mt-3 space-y-3 text-sm">
                            @if ($profile->date_of_birth)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.date_of_birth') }}</dt>
                                    <dd class="mt-0.5 text-navy">{{ $profile->date_of_birth->format('Y-m-d') }}</dd>
                                </div>
                            @endif
                            @if ($profile->gender)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.gender') }}</dt>
                                    <dd class="mt-0.5 text-navy">{{ $profile->gender->value }}</dd>
                                </div>
                            @endif
                            @if ($profile->blood_group)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.blood_group') }}</dt>
                                    <dd class="mt-0.5 text-navy">{{ $profile->blood_group->value }}</dd>
                                </div>
                            @endif
                            @if ($profile->present_address)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.present_address') }}</dt>
                                    <dd class="mt-0.5 text-navy">{{ $profile->present_address }}</dd>
                                </div>
                            @endif
                            @if ($profile->permanent_address)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.permanent_address') }}</dt>
                                    <dd class="mt-0.5 text-navy">{{ $profile->permanent_address }}</dd>
                                </div>
                            @endif
                            @if ($profile->website)
                                <div>
                                    <dt class="text-on-surface-variant">{{ __('alumkit::profile.website') }}</dt>
                                    <dd class="mt-0.5 break-all text-navy">{{ $profile->website }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($socials->isNotEmpty())
                    <div class="mt-6 border-t border-outline-variant/60 pt-5">
                        <p class="label-caps text-gold">{{ __('alumkit::profile.social_links') }}</p>
                        <dl class="mt-3 space-y-3 text-sm">
                            @foreach ($socials as $key => $url)
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="shrink-0 text-on-surface-variant">{{ $key === 'linkedin' ? __('alumkit::profile.linkedin') : __('alumkit::profile.facebook') }}</dt>
                                    <dd class="truncate text-right text-navy">{{ $url }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            @if ($emergency['name'] ?? null)
                <div class="card p-6">
                    <p class="label-caps text-gold">{{ __('alumkit::profile.emergency_contact') }}</p>
                    <p class="mt-3 text-sm font-semibold text-navy">{{ $emergency['name'] }}</p>
                    @if ($emergency['relation'] ?? null)
                        <p class="mt-0.5 text-sm text-on-surface-variant">{{ $emergency['relation'] }}</p>
                    @endif
                    @if ($emergency['phone'] ?? null)
                        <p class="mt-0.5 text-sm text-on-surface-variant">{{ $emergency['phone'] }}</p>
                    @endif
                </div>
            @endif
        </aside>

        {{-- Main narrative --}}
        <div class="space-y-6 lg:order-1 lg:col-span-8">
            @if (! $profile)
                <div class="card px-6 py-8 text-sm text-on-surface-variant">
                    {{ __('alumkit::dashboard.no_profile_data') }}
                </div>
            @endif

            <section class="card p-6 lg:p-8">
                <h2 class="font-serif text-2xl font-semibold text-navy">{{ __('alumkit::education.educations') }}</h2>
                <div class="mt-6 space-y-6">
                    @forelse ($educations as $education)
                        <div class="border-t border-outline-variant/60 pt-5 first:border-t-0 first:pt-0">
                            <p class="label-caps text-gold">
                                {{ $education->start_year }} — {{ $education->end_year ?? __('alumkit::career.present') }}
                            </p>
                            <h3 class="mt-1 font-serif text-lg font-semibold text-navy">{{ $education->institution }}</h3>
                            <p class="mt-0.5 text-on-surface-variant">
                                {{ $education->level }}{{ $education->subject ? ' · '.$education->subject : '' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">{{ __('alumkit::education.no_educations') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="card p-6 lg:p-8">
                <h2 class="font-serif text-2xl font-semibold text-navy">{{ __('alumkit::career.careers') }}</h2>
                <div class="mt-6 space-y-6">
                    @forelse ($careers as $career)
                        <div class="border-t border-outline-variant/60 pt-5 first:border-t-0 first:pt-0">
                            <p class="label-caps text-gold">
                                {{ $career->start_year }} — {{ $career->is_current ? __('alumkit::career.present') : ($career->end_year ?? '—') }}
                            </p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <h3 class="font-serif text-lg font-semibold text-navy">{{ $career->job_title }}</h3>
                                <span class="rounded bg-surface-container px-2 py-0.5 text-xs font-medium text-navy">{{ config("alumkit.career.employment_types.{$career->employment_type->value}", $career->employment_type->value) }}</span>
                            </div>
                            <p class="mt-0.5 text-on-surface-variant">{{ $career->company }}</p>
                            @if ($career->industry || $career->location)
                                <p class="mt-0.5 text-sm text-on-surface-variant">{{ $career->industry }} · {{ $career->location }}</p>
                            @endif
                            @if ($career->description)
                                <p class="mt-3 max-w-prose text-on-surface-variant">{{ $career->description }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">{{ __('alumkit::career.no_careers') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="card p-6 lg:p-8">
                <h2 class="font-serif text-2xl font-semibold text-navy">{{ __('alumkit::dashboard.membership') }}</h2>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <span class="text-sm text-on-surface-variant">{{ __('alumkit::dashboard.current_state') }}</span>
                    @include('alumkit::users.partials.state-badge', ['state' => $user->state])
                </div>

                @if ($user->getKey() !== auth()->id())
                <div class="mt-5 space-y-3">
                    @forelse ($transitions as $transition)
                        <div class="flex flex-col gap-3 rounded-lg bg-surface-container/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-on-surface-variant">{{ __("alumkit::dashboard.transition_description_{$transition->value}") }}</p>
                            <form method="POST" action="{{ route('alumkit.users.state.update', $user) }}" class="shrink-0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="state" value="{{ $transition->value }}">
                                @if ($transition->value === 'active')
                                    <button type="submit" class="inline-flex items-center justify-center rounded bg-gold px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-gold/90 focus-visible:ring-2 focus-visible:ring-gold/50">
                                        {{ __("alumkit::dashboard.transition_to_{$transition->value}") }}
                                    </button>
                                @elseif ($transition->value === 'rejected')
                                    <button type="submit" class="inline-flex items-center justify-center rounded border border-error px-4 py-2 text-sm font-semibold text-error transition-colors hover:bg-error hover:text-white focus-visible:ring-2 focus-visible:ring-error/50">
                                        {{ __("alumkit::dashboard.transition_to_{$transition->value}") }}
                                    </button>
                                @else
                                    <button type="submit" class="btn-secondary">
                                        {{ __("alumkit::dashboard.transition_to_{$transition->value}") }}
                                    </button>
                                @endif
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">{{ __('alumkit::dashboard.no_further_actions') }}</p>
                    @endforelse
                </div>
                @endif

                <div class="mt-6 border-t border-outline-variant/60 pt-5">
                    <a href="{{ route('alumkit.users.roles.edit', $user) }}" class="btn-secondary w-full">
                        {{ __('alumkit::dashboard.assign_roles') }}
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection
