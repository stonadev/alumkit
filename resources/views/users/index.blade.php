@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::dashboard.manage_user_roles') }}
    </h1>

    <form method="GET" action="{{ route('alumkit.users.index') }}" class="mb-6 rounded-lg border border-outline-variant/60 bg-white p-4 shadow-[0_4px_20px_rgba(0,33,71,0.05)]"
          x-data="{
              search: {{ Js::from($search) }},
              controller: null,
              searchUsers() {
                  if (this.controller) this.controller.abort();
                  this.controller = new AbortController();
                  const filter = document.getElementById('filter').value;
                  fetch('{{ route('alumkit.users.index') }}?filter=' + encodeURIComponent(filter) + '&search=' + encodeURIComponent(this.search), {
                      headers: { 'X-Requested-With': 'XMLHttpRequest' },
                      signal: this.controller.signal
                  })
                  .then(r => r.text())
                  .then(html => { document.getElementById('user-grid').innerHTML = html; })
                  .catch(e => { if (e.name !== 'AbortError') throw e; });
              }
          }"
          @submit.prevent>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_176px]">
            <x-input type="search" name="search" :value="$search" x-model="search" x-on:input.debounce.300ms="searchUsers()" :label="__('alumkit::dashboard.search_users')" placeholder="{{ __('alumkit::dashboard.search_users') }}" />

            <div>
                <label for="filter" class="mb-1 block text-sm font-medium text-gray-700">{{ __('alumkit::dashboard.filter_status') }}</label>
                <select name="filter" id="filter"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-on-surface shadow-sm transition-colors focus:border-navy focus:outline-none focus:ring-2 focus:ring-gold/50"
                        onchange="this.form.submit()">
                    @foreach ([
                        'pending' => __('alumkit::dashboard.filter_pending'),
                        'unverified' => __('alumkit::dashboard.filter_unverified'),
                        'active' => __('alumkit::dashboard.filter_active'),
                        'rejected' => __('alumkit::dashboard.filter_rejected'),
                        'suspended' => __('alumkit::dashboard.filter_suspended'),
                        'all' => __('alumkit::dashboard.filter_all'),
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    @php
        $total = array_sum($counts);
        if ($search !== '' && $filter === 'all') {
            $summary = __('alumkit::dashboard.summary_filtered', ['count' => $users->count()]);
        } elseif ($filter === 'all') {
            $summary = __('alumkit::dashboard.summary_all', ['count' => $total]);
        } else {
            $label = $filter === 'unverified'
                ? strtolower(__('alumkit::dashboard.filter_unverified'))
                : strtolower(__('alumkit::dashboard.state_' . $filter));
            $summary = __('alumkit::dashboard.summary_state', ['count' => $counts[$filter] ?? 0, 'state' => $label]);
        }
    @endphp

    <p class="mb-6 text-sm text-on-surface-variant">{{ $summary }}</p>

    <div id="user-grid" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @include('alumkit::users.partials.grid')
    </div>
@endsection
