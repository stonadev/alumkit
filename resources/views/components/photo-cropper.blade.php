@props([
    'name' => 'photo',
    'chooseLabel' => __('alumkit::profile.choose_photo'),
    'existing' => null,
    'initial' => null,
    'boxClass' => 'h-40 w-40',
    'iconClass' => 'h-7 w-7',
    'textClass' => 'text-sm',
])

<div x-data="photoCropper" class="inline-block">
    {{ $label ?? '' }}

    <span role="button" tabindex="0"
          aria-label="{{ $chooseLabel }}"
          class="flex {{ $boxClass }} cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-gold/50"
          x-on:click="pick()"
          x-on:keydown.enter.prevent="pick()"
          x-on:keydown.space.prevent="pick()">
        <template x-if="preview">
            <img :src="preview" alt="" class="h-full w-full object-cover">
        </template>
        <template x-if="!preview">
            @if ($existing)
                <img src="{{ $existing }}" alt="{{ $chooseLabel }}" class="h-full w-full object-cover">
            @elseif ($initial)
                <span class="flex h-full w-full items-center justify-center font-serif text-2xl font-semibold text-navy">{{ $initial }}</span>
            @else
                <span class="flex flex-col items-center gap-1.5 {{ $textClass }} text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="{{ $iconClass }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <span>{{ $chooseLabel }}</span>
                </span>
            @endif
        </template>
    </span>

    <input type="file" name="{{ $name }}" accept="image/*" class="sr-only" x-ref="input"
           x-on:change="onSelect($event)">

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div x-show="cropping" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
         x-on:keydown.escape.window="cancel()"
         x-on:click.self="cancel()">
        <div class="w-full max-w-2xl rounded-xl bg-white p-4 shadow-2xl" x-on:click.stop>
            <div class="overflow-hidden rounded-lg bg-gray-100">
                <img x-ref="cropImage" :src="cropSrc" alt="" class="block max-h-[60vh] max-w-full">
            </div>
            <div class="mt-4 flex items-center justify-end gap-3">
                <x-button type="button" x-on:click="cancel()" outline :text="__('alumkit::messages.cancel')" />
                <x-button type="button" x-on:click="save()" :text="__('alumkit::messages.crop')" />
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="{{ url('alumkit/style/alumkit-cropper.css') }}">
    <script defer src="{{ url('alumkit/style/alumkit-cropper.js') }}"></script>
</div>
