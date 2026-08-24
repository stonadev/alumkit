@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false, 'placeholder' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
            class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50">
        @if ($placeholder !== null)
            <option value="" @selected(blank($value))>{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($value === $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
