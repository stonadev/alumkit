@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::post.edit_post') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <x-input name="title" :label="__('alumkit::post.title')" :value="$post->title" required />

                <div x-data="{ thumbnailPreview: null }">
                    <span class="dark:text-dark-400 mb-1 block text-sm font-semibold text-gray-600">{{ __('alumkit::post.thumbnail') }}</span>
                    <span role="button" tabindex="0"
                          class="flex h-40 w-40 cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-gold/50"
                          x-on:click="$refs.thumbnailInput.click()"
                          x-on:keydown.enter.prevent="$refs.thumbnailInput.click()"
                          x-on:keydown.space.prevent="$refs.thumbnailInput.click()">
                        <template x-if="thumbnailPreview">
                            <img :src="thumbnailPreview" alt="" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!thumbnailPreview">
                            @if ($post->thumbnailUrl())
                                <img src="{{ $post->thumbnailUrl() }}" alt="{{ __('alumkit::post.thumbnail') }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex flex-col items-center gap-1.5 text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    <span>{{ __('alumkit::post.select_thumbnail') }}</span>
                                </span>
                            @endif
                        </template>
                    </span>
                    <input type="file" name="thumbnail" accept="image/*" hidden x-ref="thumbnailInput"
                           x-on:change="thumbnailPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-alumkit::editor-field name="body" :label="__('alumkit::post.body')" :value="$post->body" />
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-alumkit::checkbox name="published" :label="__('alumkit::post.publish')" :checked="$post->isPublished()" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::post.edit_post')" />
                <a href="{{ route('alumkit.posts.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
