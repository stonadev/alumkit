@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::post.edit_post') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.posts.update', $post) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <x-input name="title" :label="__('alumkit::post.title')" :value="$post->title" required />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('alumkit::post.body') }}
                    </label>
                    <textarea name="body" rows="8" class="w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">{{ $post->body }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" name="published" value="1" @checked($post->isPublished()) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('alumkit::post.publish') }}</span>
                </label>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::post.edit_post')" />
                <a href="{{ route('alumkit.posts.index') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
