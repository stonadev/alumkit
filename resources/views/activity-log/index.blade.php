@php
    $userClass = app('auth')->getProvider()->getModel();

    $domains = [
        'member_management' => __('alumkit::activity_log.domain_member_management'),
        'role_management' => __('alumkit::activity_log.domain_role_management'),
        'profile' => __('alumkit::activity_log.domain_profile'),
    ];

    $verbs = [
        'member_management' => [
            'state_changed' => __('alumkit::activity_log.state_changed'),
            'roles_synced' => __('alumkit::activity_log.roles_synced'),
        ],
        'role_management' => [
            'created' => __('alumkit::activity_log.role_created'),
            'updated' => __('alumkit::activity_log.role_updated'),
            'deleted' => __('alumkit::activity_log.role_deleted'),
        ],
        'profile' => [
            'submitted' => __('alumkit::activity_log.profile_submitted'),
            'resubmitted' => __('alumkit::activity_log.profile_resubmitted'),
        ],
    ];

    $subjectLabels = [
        'member_management' => __('alumkit::activity_log.subject_member'),
        'role_management' => __('alumkit::activity_log.subject_role'),
        'profile' => __('alumkit::activity_log.subject_profile'),
    ];

    $subjectLinks = [
                'member_management' => fn ($subject) => $subject instanceof $userClass
            ? route('alumkit.users.show', $subject)
            : null,
        'role_management' => fn ($subject) => $subject instanceof \Spatie\Permission\Models\Role
            ? route('alumkit.roles.edit', $subject)
            : null,
                'profile' => fn ($subject) => $subject instanceof $userClass
            ? route('alumkit.users.show', $subject)
            : null,
    ];
@endphp

@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="space-y-12">
        {{-- Header --}}
        <section class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <p class="label-caps text-gold">{{ __('alumkit::activity_log.overline') }}</p>
                <h1 class="mt-3 font-serif text-3xl sm:text-4xl font-semibold text-navy">
                    {{ __('alumkit::activity_log.title') }}
                </h1>
                <p class="mt-4 text-lg leading-8 text-on-surface-variant">
                    {{ __('alumkit::activity_log.subtitle') }}
                </p>
            </div>
        </section>

        @if ($activities->isEmpty())
            {{-- Empty state: an invitation, not a dead end --}}
            <section class="card flex flex-col items-center justify-center px-6 py-20 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-navy/5" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="mt-6 font-serif text-xl font-semibold text-navy">
                    {{ __('alumkit::activity_log.empty_title') }}
                </h2>
                <p class="mt-2 max-w-md text-sm leading-6 text-on-surface-variant">
                    {{ __('alumkit::activity_log.empty_body') }}
                </p>
            </section>
        @else
            {{-- The ledger: gold rail timeline, one entry per line --}}
            <section aria-label="{{ __('alumkit::activity_log.title') }}">
                <ol class="space-y-6">
                    @foreach ($activities as $activity)
                        @php
                            $domain = $activity->log_name ?? 'default';
                            $verb = $verbs[$domain][$activity->event] ?? $activity->event;
                            $subject = $activity->subject;
                            $subjectLabel = $subjectLabels[$domain] ?? __('alumkit::activity_log.subject_member');
                            $subjectLink = isset($subjectLinks[$domain]) ? $subjectLinks[$domain]($subject) : null;
                            $props = $activity->properties;
                            $causerName = $activity->causer?->name ?? __('alumkit::activity_log.unknown_actor');
                        @endphp
                        <li class="relative border-l-4 border-gold pl-8">
                            <div class="card p-6">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                    <p class="label-caps text-gold">{{ $domains[$domain] ?? $domain }}</p>
                                    <time datetime="{{ $activity->created_at->toIso8601String() }}"
                                          class="text-sm text-on-surface-variant">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="mt-3 font-serif text-lg font-semibold text-navy">
                                    {{ $verb }}
                                </p>
                                <p class="mt-2 text-sm leading-6 text-on-surface-variant">
                                    <span class="font-semibold text-navy">{{ $causerName }}</span>
                                    @if ($subject !== null)
                                        {{ __('alumkit::activity_log.actor_on_subject', ['label' => $subjectLabel]) }}
                                        @if ($subjectLink !== null)
                                            <a href="{{ $subjectLink }}" class="font-semibold text-navy underline decoration-gold/50 underline-offset-2 transition-colors hover:text-gold">
                                                {{ $subject->name ?? $subject->email ?? $subjectLabel }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-navy">{{ $subject->name ?? $subject->email ?? $subjectLabel }}</span>
                                        @endif
                                    @endif
                                </p>

                                @if ($props !== null && $props->isNotEmpty())
                                    <dl class="mt-4 grid gap-x-8 gap-y-2 sm:grid-cols-2">
                                        @foreach ($props as $key => $value)
                                            <div class="flex items-baseline gap-2">
                                                <dt class="label-caps shrink-0 text-on-surface-variant">{{ $key }}</dt>
                                                <dd class="truncate text-sm text-on-surface-variant">
                                                    @if (is_array($value))
                                                        @if (empty($value))
                                                            <span class="italic opacity-70">{{ __('alumkit::activity_log.none') }}</span>
                                                        @else
                                                            {{ implode(', ', array_map('strval', $value)) }}
                                                        @endif
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{ $activities->links('alumkit.pagination::simple') }}
            </section>
        @endif
    </div>
@endsection
