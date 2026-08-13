@php
    $colors = match ($state) {
        'pending' => 'bg-yellow-100 text-yellow-800',
        'active' => 'bg-green-100 text-green-800',
        'suspended' => 'bg-red-100 text-red-800',
        'rejected' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }}">
    {{ __("alumkit::dashboard.state_{$state}") }}
</span>
