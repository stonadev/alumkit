@php
    $colors = match ($state) {
        'unverified' => 'bg-orange-100 text-orange-700',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'active' => 'bg-green-100 text-green-800',
        'suspended' => 'bg-red-100 text-red-800',
        'rejected' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-800',
    };
    $label = __("alumkit::dashboard.state_{$state}");
@endphp

<span class="inline-flex items-center gap-1">
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }}">
        {{ $label }}
    </span>
</span>
