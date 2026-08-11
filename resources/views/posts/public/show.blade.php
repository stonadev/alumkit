@extends('alumkit::layouts.public')

@section('content')
    <x-card>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $post->title }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('alumkit::post.author') }}: {{ $post->user->name }} &middot; {{ $post->published_at->format('M j, Y') }}
        </p>
        <div class="mt-6 text-gray-800 dark:text-gray-200">
            {!! nl2br(e($post->body)) !!}
        </div>
    </x-card>

    <div class="mt-6">
        <a href="{{ route('alumkit.posts.public.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; {{ __('alumkit::post.posts') }}
        </a>
    </div>
@endsection
