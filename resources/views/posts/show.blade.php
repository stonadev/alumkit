@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-navy">
                {{ $post->title }}
            </h1>

            @if ($post->isPublished())
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ __('alumkit::post.published') }}
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ __('alumkit::post.draft') }}
                </span>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route('alumkit.posts.edit', $post) }}" class="text-navy hover:text-gold">
                {{ __('alumkit::dashboard.edit') }}
            </a>

            <a href="{{ route('alumkit.posts.index') }}" class="text-gray-600 hover:text-navy">
                &larr; {{ __('alumkit::post.my_posts') }}
            </a>
        </div>
    </div>

    <x-card>
        @if ($post->thumbnailUrl())
            <img src="{{ $post->thumbnailUrl() }}" alt="{{ $post->title }}" class="w-full h-64 object-cover rounded-lg mb-6">
        @endif

        <div class="prose-navy">
            {!! $post->bodyHtml() !!}
        </div>
    </x-card>
@endsection
