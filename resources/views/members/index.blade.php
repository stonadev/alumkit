@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::dashboard.member_directory') }}
    </h1>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($users as $u)
            @php
                $profile = $u->profile;
                $latestEducation = $profile ? $profile->educations->sortByDesc('start_year')->first() : null;
                $career = $profile ? ($profile->careers->firstWhere('is_current', true) ?? $profile->careers->first()) : null;
            @endphp

            <x-card>
                <a href="{{ route('alumkit.members.show', $u) }}" class="group flex h-full flex-col">
                    <div class="flex items-center gap-4">
                        @if ($profile?->photoUrl())
                            <img src="{{ $profile->photoUrl() }}" alt="{{ $u->name }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-surface-container text-lg font-semibold text-navy">
                                {{ \Illuminate\Support\Str::initials($u->name) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-lg font-semibold text-navy">{{ $u->name }}</h2>
                            <p class="truncate text-sm text-on-surface-variant">{{ $u->email }}</p>
                        </div>
                    </div>

                    <div class="flex-1">
                        @if ($latestEducation)
                            <p class="mt-3 text-sm text-on-surface-variant">{{ $latestEducation->level }} · {{ $latestEducation->institution }}</p>
                        @endif
                        @if ($career)
                            <p class="mt-1 text-sm text-on-surface-variant">{{ $career->job_title }} · {{ $career->company }}</p>
                        @endif
                    </div>

                    <div class="mt-4 border-t pt-4">
                        <span class="text-navy transition-colors group-hover:text-gold">
                            {{ __('alumkit::dashboard.view_profile') }}
                        </span>
                    </div>
                </a>
            </x-card>
        @empty
            <p class="col-span-full text-sm text-on-surface-variant">
                {{ __('alumkit::dashboard.no_users') }}
            </p>
        @endforelse
    </div>
@endsection
