@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'required' => false])

<div>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" rows="{{ $rows }}" @required($required)
              class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50">{{ $value }}</textarea>
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
