@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false])

<div>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" @required($required)
            class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50">
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($value === $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
