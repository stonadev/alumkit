@props(['name' => null, 'value' => null, 'label' => null, 'required' => false, 'placeholder' => '—', 'showError' => true])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @required($required)
        {{ $attributes->class('w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50 dark:bg-gray-800 dark:border-gray-700 dark:text-white') }}
    >
        <option value="" @selected(blank($value))>{{ $placeholder }}</option>
        @foreach (range((int) date('Y') + 5, 1991) as $year)
            <option value="{{ $year }}" @selected((string) $value === (string) $year)>{{ $year }}</option>
        @endforeach
    </select>
    @if ($showError)
        <x-alumkit::input-error :name="$name" />
    @endif
</div>
