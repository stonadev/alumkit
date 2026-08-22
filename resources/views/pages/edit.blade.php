@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">Edit Page</h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.pages.update', $page) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <x-input name="title" label="Title" :value="old('title', $page->title)" required />

                <x-input name="slug" label="Slug" :value="old('slug', $page->slug)" required />

                <x-input name="meta_title" label="Meta Title" :value="old('meta_title', $page->meta_title)" />

                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-gold focus:ring-gold">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>

                <x-alumkit::checkbox name="is_published" label="Published" :checked="old('is_published', $page->is_published)" />
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" text="Update Page" />
                <a href="{{ route('alumkit.pages.index') }}" class="text-gray-600 hover:text-navy">
                    Back to Pages
                </a>
            </div>
        </form>
    </x-card>
@endsection
