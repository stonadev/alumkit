<div x-data="{ open: @js($showResults) }" @click.outside="open = false" @keydown.escape.window="open = false">
    <input type="hidden" name="{{ $name }}" value="{{ $selectedUserId }}">

    @if ($fieldLabel)
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $fieldLabel }}</label>
    @endif

    @if ($selectedUserId)
        <div class="flex items-center gap-3 rounded-md border border-gray-300 bg-gray-50 px-3 py-2">
            <span class="text-sm text-navy font-medium">{{ $query }}</span>
            <button type="button" wire:click="clear" class="ml-auto text-red-600 hover:text-red-900 text-xs">
                {{ __('alumkit::dashboard.delete') }}
            </button>
        </div>
    @else
        <div class="relative">
            <input type="text" x-ref="searchInput" wire:model.live.debounce.300ms="query"
                   placeholder="{{ __('alumkit::committee.search_members') }}"
                   class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50"
                   x-on:focus="open = true"
                   autocomplete="off">

            @if ($showResults && $results)
                <ul x-show="open" x-cloak
                    class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto">
                    @foreach ($results as $result)
                        <li>
                            <button type="button" wire:click="select({{ $result['id'] }}, @js($result['name'].' — '.$result['email']))"
                                    class="w-full text-left px-3 py-2 hover:bg-surface-container text-sm">
                                <span class="block font-medium text-navy">{{ $result['name'] }}</span>
                                <span class="block text-xs text-on-surface-variant">{{ $result['email'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($showResults && $query && strlen($query) >= 2 && empty($results))
                <ul x-show="open" x-cloak
                    class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg">
                    <li class="px-3 py-2 text-sm text-gray-500">
                        {{ __('alumkit::committee.no_results') }}
                    </li>
                </ul>
            @endif
        </div>
    @endif
</div>
