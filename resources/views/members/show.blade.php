@extends('alumkit::layouts.dashboard')

@section('content')
    @php
        $profile = $user->profile;
        $educations = collect($profile?->educations ?? [])->sortByDesc('start_year');
        $careers = collect($profile?->careers ?? [])->sortByDesc('start_year');
        $currentCareer = $careers->firstWhere('is_current', true) ?? $careers->first();
        $socials = collect($profile?->social_links ?? [])->filter();
        $emergency = $profile?->emergency_contact ?? [];
    @endphp

    <a href="{{ route('alumkit.members.index') }}" class="mb-6 inline-block text-sm text-navy hover:text-gold">
        {{ __('alumkit::dashboard.back_to_members') }}
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
                        <div class="mt-3 space-y-3 text-sm">
                            @foreach ($socials as $key => $url)
                                @php
                                    $href = \Illuminate\Support\Str::startsWith($url, ['http://', 'https://']) ? $url : 'https://'.$url;
                                @endphp
                                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 text-navy transition-colors hover:text-gold" title="{{ $href }}">
                                        @if ($key === 'linkedin')
                                            <svg class="h-4 w-4 shrink-0 text-on-surface-variant" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 1 1 0-4.124 2.062 2.062 0 0 1 0 4.124zM7.119 20.452H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4 shrink-0 text-on-surface-variant" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        @endif
                                        <span class="truncate">{{ $key === 'linkedin' ? __('alumkit::profile.linkedin') : __('alumkit::profile.facebook') }}</span>
                                    </a>
                            @endforeach
                        </div>
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
                                {{ $education->start_year }} — {{ $education->end_year ?? __('alumkit::education.present') }}
                            </p>
                            <h3 class="mt-1 font-serif text-lg font-semibold text-navy">{{ $education->institution }}</h3>
                            <p class="mt-0.5 text-on-surface-variant">
                                {{ $education->level }}{{ $education->subject ? ' · '.$education->subject : '' }}
                            </p>
                            @if ($education->student_id)
                                <p class="mt-0.5 text-sm text-on-surface-variant">
                                    {{ __('alumkit::education.student_id') }}: {{ $education->student_id }}
                                </p>
                            @endif
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
        </div>
    </div>
@endsection
