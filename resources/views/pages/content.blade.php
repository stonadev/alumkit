@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">Edit Content: {{ $page->title }}</h1>
        <a href="{{ route('alumkit.pages.index') }}" class="text-gray-600 hover:text-navy">
            Back to Pages
        </a>
    </div>

    <form method="POST" action="{{ route('alumkit.pages.content.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            @foreach ($schema->sections() as $type => $section)
                <x-card>
                    <h2 class="text-xl font-semibold text-navy mb-4">{{ ucfirst($type) }}</h2>

                    <div class="space-y-4">
                        @php
                            $sectionContent = $contents->get($type);
                            $fieldValues = $sectionContent ? $sectionContent->fields : [];
                        @endphp

                        @foreach ($section->fields() as $field)
                            @php
                                $fieldName = "fields[{$field->name}]";
                                $fieldValue = $fieldValues[$field->name] ?? '';
                            @endphp

                            @if ($field->type === 'text')
                                <x-input :name="$fieldName" :label="$field->label ?? ucfirst($field->name)" :value="$fieldValue" :required="$field->required" />
                            @elseif ($field->type === 'textarea')
                                <div>
                                    <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label ?? ucfirst($field->name) }}
                                        @if ($field->required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <textarea name="{{ $fieldName }}" id="{{ $field->name }}" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring-gold" @if ($field->required) required @endif>{{ $fieldValue }}</textarea>
                                </div>
                            @elseif ($field->type === 'select')
                                <div>
                                    <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label ?? ucfirst($field->name) }}
                                        @if ($field->required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <select name="{{ $fieldName }}" id="{{ $field->name }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring-gold" @if ($field->required) required @endif>
                                        <option value="">Select...</option>
                                        @foreach (($field->options ?? []) as $value => $label)
                                            <option value="{{ $value }}" @selected($fieldValue === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif ($field->type === 'image')
                                <div x-data="{ preview: null }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label ?? ucfirst($field->name) }}
                                        @if ($field->required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    @if ($fieldValue)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $fieldValue) }}" alt="" class="h-20 w-20 object-cover rounded">
                                        </div>
                                    @endif
                                    <div x-show="preview" class="mb-2">
                                        <img :src="preview" alt="" class="h-20 w-20 object-cover rounded">
                                    </div>
                                    <input type="file" name="{{ $fieldName }}" accept="image/*" class="text-sm" x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                </div>
                            @elseif ($field->type === 'checkbox')
                                <x-alumkit::checkbox :name="$fieldName" :label="$field->label ?? ucfirst($field->name)" :checked="$fieldValue" />
                            @elseif ($field->type === 'editor')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label ?? ucfirst($field->name) }}
                                        @if ($field->required) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <x-alumkit::editor-field :name="$fieldName" :value="$fieldValue" />
                                </div>
                            @elseif ($field->type === 'repeater')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field->label ?? ucfirst($field->name) }}
                                    </label>
                                    <livewire:alumkit.repeater-field :name="$fieldName" :fields="$field->fields ?? []" :rows="$fieldValue" />
                                </div>
                            @endif

                            @if ($field->help)
                                <p class="text-sm text-gray-500 mt-1">{{ $field->help }}</p>
                            @endif
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            <x-button type="submit" text="Save Content" />
        </div>
    </form>
@endsection
