<div>
    @if ($fieldLabel)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $fieldLabel }}
        </label>
    @endif

    <input type="hidden" name="{{ $name }}[label]" value="{{ $label }}">
    <input type="hidden" name="{{ $name }}[url]" value="{{ $url }}">

    @if ($url)
        <div class="flex items-center gap-4">
            <a href="{{ $url }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:text-indigo-500">
                {{ $label ?: $url }}
            </a>
            <button type="button" wire:click="$set('showModal', true)" class="text-blue-600 hover:text-blue-900">
                {{ __('alumkit::link-field.edit_link') }}
            </button>
            <button type="button" wire:click="clear" class="text-red-600 hover:text-red-900">
                {{ __('alumkit::link-field.remove_link') }}
            </button>
        </div>
    @else
        <x-button wire:click="$set('showModal', true)" :text="__('alumkit::link-field.add_link')" />
    @endif

    <x-modal :id="'alumkit-link-field-modal-'.$this->getId()" :wire="'showModal'"
             :title="__('alumkit::link-field.link')"
             x-on:open="$tsui.focus('alumkit-link-field-url-'.$this->getId())">
        <div class="space-y-4">
            <x-input wire:model="label" :label="__('alumkit::link-field.label')" />

            <x-input :id="'alumkit-link-field-url-'.$this->getId()" wire:model.live.debounce.250ms="url"
                     :label="__('alumkit::link-field.url')" placeholder="https://" />

            @if ($url && empty($suggestions))
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('alumkit::link-field.no_matching_routes') }}
                </p>
            @endif

            @if ($suggestions)
                <ul class="space-y-2">
                    @foreach ($suggestions as $suggestion)
                        <li :wire:key="'suggestion-'.$suggestion['name']">
                            <button type="button" wire:click="pickRoute('{{ $suggestion['name'] }}')" class="w-full text-left">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $suggestion['name'] }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $suggestion['url'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <x-slot:footer>
            <x-button outline x-on:click="$tsui.close.modal('alumkit-link-field-modal-'.$this->getId())" :text="__('alumkit::link-field.cancel')" />
            <button type="button" wire:click="save" :disabled="! $url"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50">
                {{ __('alumkit::link-field.save') }}
            </button>
        </x-slot:footer>
    </x-modal>
</div>
