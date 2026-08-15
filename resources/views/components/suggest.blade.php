@props(['name' => null, 'label' => null, 'value' => null, 'suggestions' => [], 'required' => false])

<div
    x-data="{
        suggestions: {{ Js::from($suggestions) }},
        value: '',
        open: false,
        highlighted: -1,
        get filtered() {
            const q = this.value.trim().toLowerCase();
            return this.suggestions.filter((s) => !q || s.toLowerCase().includes(q));
        },
        onInput(event) {
            this.value = event.target.value;
            this.open = true;
        },
        select(s) {
            this.value = s;
            const input = this.$refs.input;
            input.value = s;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            this.open = false;
            this.highlighted = -1;
        },
        onKeydown(event) {
            if (!this.open || this.filtered.length === 0) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                event.stopPropagation();
                this.highlighted = (this.highlighted + 1) % this.filtered.length;
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                event.stopPropagation();
                this.highlighted = (this.highlighted - 1 + this.filtered.length) % this.filtered.length;
            } else if (event.key === 'Enter' && this.highlighted >= 0) {
                event.preventDefault();
                event.stopPropagation();
                this.select(this.filtered[this.highlighted]);
            } else if (event.key === 'Escape') {
                event.stopPropagation();
                this.open = false;
                this.highlighted = -1;
            }
        }
    }"
    class="relative"
>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <input
        x-ref="input"
        type="text"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        @required($required)
        autocomplete="off"
        x-on:focus="open = true; highlighted = -1"
        x-on:blur="open = false; highlighted = -1"
        x-on:input="onInput($event)"
        x-on:keydown="onKeydown($event)"
        class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-navy focus:ring-gold/50"
        {{ $attributes }}
    >

    <div
        x-show="open && filtered.length"
        x-cloak
        role="listbox"
        class="absolute inset-x-0 top-full z-10 mt-1 overflow-hidden rounded-md border border-gray-300 bg-white py-1 shadow-lg"
    >
        <template x-for="(s, i) in filtered" :key="s">
            <button
                type="button"
                role="option"
                :aria-selected="i === highlighted ? 'true' : 'false'"
                x-on:mousedown.prevent="select(s)"
                x-on:mousemove="highlighted = i"
                :class="i === highlighted ? 'bg-navy text-white' : 'text-gray-700'"
                class="block w-full px-3 py-2 text-left text-sm"
                x-text="s"
            ></button>
        </template>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
