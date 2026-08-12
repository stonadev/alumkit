@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::post.new_post') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.posts.store') }}">
            @csrf

            <div class="space-y-4">
                <x-input name="title" :label="__('alumkit::post.title')" :value="old('title')" required />

                <div>
                    <x-alumkit::editor-field name="body" :label="__('alumkit::post.body')" :value="old('body')" />
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" name="published" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('alumkit::post.publish') }}</span>
                </label>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::post.new_post')" />
                <a href="{{ route('alumkit.posts.index') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
