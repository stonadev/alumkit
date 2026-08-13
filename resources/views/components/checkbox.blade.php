@props(['name', 'label' => null, 'value' => 1, 'uncheckedValue' => 0, 'checked' => false])

<label class="flex items-center gap-2">
    <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked)
           {{ $attributes->class(['rounded border-gray-300 text-navy focus:ring-gold/50']) }}>
    @if ($label)
        <span class="text-sm text-gray-700">{{ $label }}</span>
    @endif
</label>
