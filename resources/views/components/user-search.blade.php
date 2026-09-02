@props(['name', 'label' => null, 'value' => null])

<livewire:alumkit.user-search :name="$name" :fieldLabel="$label" :selectedUserId="$value" wire:key="user-search-{{ $name }}" />
