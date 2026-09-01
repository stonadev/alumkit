@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::dashboard.manage_user_roles') }}
    </h1>

    <form method="GET" action="{{ route('alumkit.users.index') }}" class="mb-6 max-w-sm" x-data="{
        search: {{ Js::from($search) }},
        debounce: null,
        searchUsers() {
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                fetch('{{ route('alumkit.users.index') }}?filter={{ $filter }}&search=' + encodeURIComponent(this.search), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(r => r.text())
                    .then(html => { document.getElementById('user-grid').innerHTML = html; })
                    .catch(() => {});
            }, 300);
        }
    }" @submit.prevent>
        <input type="hidden" name="filter" value="{{ $filter }}">
        <x-input type="search" name="search" x-model="search" x-on:input.debounce.300ms="searchUsers()" :value="$search" placeholder="{{ __('alumkit::dashboard.search_users') }}" />
    </form>

    <nav class="mb-6 flex gap-1 border-b border-outline-variant/60">
        @foreach ([
            'all' => __('alumkit::dashboard.filter_all'),
            'pending' => __('alumkit::dashboard.filter_pending'),
            'active' => __('alumkit::dashboard.filter_active'),
            'rejected' => __('alumkit::dashboard.filter_rejected'),
            'suspended' => __('alumkit::dashboard.filter_suspended'),
        ] as $value => $label)
            <a href="{{ route('alumkit.users.index', array_filter(['filter' => $value, 'search' => $search])) }}"
               class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors {{ $filter === $value ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div id="user-grid" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @include('alumkit::users.partials.grid')
    </div>
@endsection
