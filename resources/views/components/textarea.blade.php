@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'required' => false, 'showError' => true])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50">{{ $value }}</textarea>
    @if ($showError)
        <x-alumkit::input-error :name="$name" />
    @endif
</div>
