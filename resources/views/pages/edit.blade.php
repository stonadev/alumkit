@extends('alumkit::layouts.dashboard')

@section('content')
    {{-- Header: page title with inline publish status --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <h1 class="text-2xl font-bold text-navy">{{ $page->title }}</h1>
        @if ($page->is_published)
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-800/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-800"></span>
                Published
            </span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-surface-container px-2.5 py-0.5 text-xs font-semibold text-on-surface-variant">
                Draft
            </span>
        @endif
        <a href="{{ route('alumkit.pages.index') }}" class="ml-auto text-sm font-medium text-on-surface-variant hover:text-navy transition-colors">
            Back to Pages
        </a>
    </div>

    <form method="POST" action="{{ route('alumkit.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div
            x-data="{
                tab: 'content',
                tabs: ['content', 'meta'],
                syncTab() { this.tab = this.tabs.includes((location.hash || '#content').slice(1)) ? (location.hash || '#content').slice(1) : 'content'; },
                moveTab(direction) {
                    const index = this.tabs.indexOf(this.tab);
                    document.getElementById('tab-' + this.tabs[(index + direction + this.tabs.length) % this.tabs.length])?.focus();
                },
            }"
            x-init="syncTab()"
            @hashchange.window="syncTab()"
        >
            {{-- Tab bar --}}
            <nav role="tablist" aria-label="Page sections" @keydown.arrow-right.prevent="moveTab(1)" @keydown.arrow-left.prevent="moveTab(-1)" class="mb-8 flex gap-1 border-b border-outline-variant/60">
                <a href="#content" id="tab-content" role="tab" aria-controls="content" :aria-selected="tab === 'content'" :tabindex="tab === 'content' ? 0 : -1"
                   :class="tab === 'content' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">Main</a>

                <a href="#meta" id="tab-meta" role="tab" aria-controls="meta" :aria-selected="tab === 'meta'" :tabindex="tab === 'meta' ? 0 : -1"
                   :class="tab === 'meta' ? 'text-navy border-gold' : 'text-on-surface-variant hover:text-navy border-transparent'"
                   class="border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors">Meta</a>
            </nav>

            {{-- Main tab: page details + schema-driven fields --}}
            <section id="content" role="tabpanel" aria-labelledby="tab-content" x-show="tab === 'content'" x-cloak>
                {{-- Page details --}}
                <x-card>
                    <p class="label-caps text-on-surface-variant mb-1">page</p>
                    <h2 class="text-lg font-semibold text-navy mb-5">Page Details</h2>
                    <div class="space-y-5">
                        <x-input name="title" label="Title" :value="old('title', $page->title)" required />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                            <p class="text-sm text-on-surface-variant py-2">{{ $page->slug }}</p>
                        </div>
                    </div>
                </x-card>

                {{-- Schema-driven fields --}}
                @if ($schema)
                    <div class="space-y-8 mt-8">
                        @foreach ($schema->sections() as $type => $section)
                            <x-card>
                                <p class="label-caps text-on-surface-variant mb-1">{{ $type }}</p>
                                <h2 class="text-lg font-semibold text-navy mb-5">{{ $section->label ?? ucfirst($type) }}</h2>

                                <div class="space-y-5">
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
                                                    @if ($field->required) <span class="text-error">*</span> @endif
                                                </label>
                                                <textarea name="{{ $fieldName }}" id="{{ $field->name }}" rows="3"
                                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy focus:ring-gold/50"
                                                          @if ($field->required) required @endif>{{ $fieldValue }}</textarea>
                                            </div>

                                        @elseif ($field->type === 'select')
                                            <div>
                                                <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-1">
                                                    {{ $field->label ?? ucfirst($field->name) }}
                                                    @if ($field->required) <span class="text-error">*</span> @endif
                                                </label>
                                                <select name="{{ $fieldName }}" id="{{ $field->name }}"
                                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy focus:ring-gold/50"
                                                        @if ($field->required) required @endif>
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
                                                    @if ($field->required) <span class="text-error">*</span> @endif
                                                </label>
                                                @if ($fieldValue)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $fieldValue) }}" alt="" class="h-20 w-20 rounded-lg object-cover ring-1 ring-outline-variant/40">
                                                    </div>
                                                @endif
                                                <div x-show="preview" class="mb-2">
                                                    <img :src="preview" alt="" class="h-20 w-20 rounded-lg object-cover ring-1 ring-outline-variant/40">
                                                </div>
                                                <input type="file" name="{{ $fieldName }}" accept="image/*"
                                                       class="block w-full text-sm text-on-surface-variant file:mr-4 file:rounded-md file:border-0 file:bg-surface-container file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-navy hover:file:bg-outline-variant/50"
                                                       x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                            </div>

                                        @elseif ($field->type === 'checkbox')
                                            <x-alumkit::checkbox :name="$fieldName" :label="$field->label ?? ucfirst($field->name)" :checked="$fieldValue" />

                                        @elseif ($field->type === 'editor')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    {{ $field->label ?? ucfirst($field->name) }}
                                                    @if ($field->required) <span class="text-error">*</span> @endif
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
                                            <p class="text-sm text-on-surface-variant mt-1">{{ $field->help }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            </x-card>
                        @endforeach
                    </div>
                @else
                    <x-card class="mt-8">
                        <p class="text-on-surface-variant">
                            No content schema registered for "{{ $page->slug }}". Register one with
                            <code class="rounded bg-surface-container px-1.5 py-0.5 text-sm font-medium">Alumkit::page(...)</code>
                            to edit page content here.
                        </p>
                    </x-card>
                @endif
            </section>

            {{-- Meta tab --}}
            <section id="meta" role="tabpanel" aria-labelledby="tab-meta" x-show="tab === 'meta'" x-cloak>
                <x-card>
                    <div class="space-y-5">
                        <x-input name="meta_title" label="Meta Title" :value="old('meta_title', $page->meta_title)" />

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-navy focus:ring-gold/50">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>
                    </div>
                </x-card>
            </section>
        </div>

        {{-- Publish toggle + save actions --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-outline-variant/60 bg-surface px-4 py-3">
            <div class="flex items-center gap-3" x-data="{ isPublished: @js(old('is_published', $page->is_published)) }">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" id="publish-toggle" class="peer sr-only" aria-label="Toggle page publish status" x-model="isPublished" @checked(old('is_published', $page->is_published))>
                <label for="publish-toggle" class="ak-toggle"></label>
                <span class="text-sm font-medium text-navy" x-text="isPublished ? 'Published' : 'Draft'">Published</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('alumkit.pages.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
@endsection
