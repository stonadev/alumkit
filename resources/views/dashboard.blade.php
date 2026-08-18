@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="space-y-12">
        @if (Auth::user()->state === \Alumkit\Alumkit\Enums\UserState::Suspended->value)
            <div class="rounded-lg border border-error/25 bg-error/5 px-4 py-3 text-sm text-error">
                {{ __('alumkit::dashboard.account_suspended') }}
            </div>
        @endif

        {{-- Hero --}}
        <section class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                <p class="label-caps text-gold">Alumni Network</p>
                <h1 class="mt-3 font-serif text-3xl sm:text-4xl font-semibold text-navy">
                    {{ __('alumkit::dashboard.welcome_back', ['name' => Auth::user()->name]) }}
                </h1>
                <p class="mt-4 text-lg leading-8 text-on-surface-variant">
                    {{ __('alumkit::dashboard.welcome_text') }}
                </p>
            </div>
            @include('alumkit::users.partials.state-badge', ['state' => Auth::user()->state])
        </section>

        {{-- Quick links --}}
        @php
            $links = [
                ['route' => 'alumkit.roles.index', 'show' => auth()->user()->can('manage roles'), 'overline' => __('alumkit::dashboard.roles'), 'title' => __('alumkit::dashboard.roles'), 'description' => __('alumkit::dashboard.manage_roles_description')],
                ['route' => 'alumkit.users.index', 'show' => auth()->user()->can('manage members'), 'overline' => __('alumkit::dashboard.manage_user_roles'), 'title' => __('alumkit::dashboard.manage_user_roles'), 'description' => __('alumkit::dashboard.manage_members_description')],
                ['route' => 'alumkit.careers.index', 'show' => auth()->user()->can('manage careers'), 'overline' => __('alumkit::career.careers'), 'title' => __('alumkit::career.careers'), 'description' => __('alumkit::dashboard.careers_description')],
                ['route' => 'alumkit.posts.index', 'show' => auth()->user()->state === \Alumkit\Alumkit\Enums\UserState::Active->value, 'overline' => __('alumkit::post.posts'), 'title' => __('alumkit::post.posts'), 'description' => __('alumkit::dashboard.posts_description')],
            ];
            $links = array_values(array_filter($links, fn ($link) => $link['show']));
        @endphp

        @if (Auth::user()->state !== \Alumkit\Alumkit\Enums\UserState::Suspended->value && ! empty($links))
            <section>
                <h2 class="font-serif text-xl font-semibold text-navy">
                    {{ __('alumkit::dashboard.quick_links') }}
                </h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($links as $link)
                        <a href="{{ route($link['route']) }}"
                           class="card group p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_6px_24px_rgba(0,33,71,0.08)]">
                            <p class="label-caps text-gold">{{ $link['overline'] }}</p>
                            <h3 class="mt-2 font-serif text-xl font-semibold text-navy">{{ $link['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-on-surface-variant">{{ $link['description'] }}</p>
                            <p class="mt-4 text-sm font-semibold text-navy transition-colors group-hover:text-gold">
                                {{ __('alumkit::dashboard.open') }} →
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if (Auth::user()->state !== \Alumkit\Alumkit\Enums\UserState::Suspended->value)
        {{-- Heritage accent: high-prestige content --}}
        <section class="card border-l-4 border-gold p-8 sm:p-10">
            <p class="label-caps text-gold">Heritage</p>
            <h2 class="mt-2 font-serif text-2xl font-semibold text-navy">
                {{ __('alumkit::dashboard.manage_your_profile') }}
            </h2>
            <p class="mt-3 max-w-2xl leading-7 text-on-surface-variant">
                {{ __('alumkit::dashboard.profile_cta') }}
            </p>
            <a href="{{ route('alumkit.profile') }}" class="btn-primary mt-6">
                {{ __('alumkit::dashboard.view_profile') }}
            </a>
        </section>
        @endif
    </div>
@endsection
