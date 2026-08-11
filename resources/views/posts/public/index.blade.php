@extends('alumkit::layouts.public')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        {{ __('alumkit::post.posts') }}
    </h1>

    @if ($posts->isEmpty())
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('alumkit::post.no_published_posts') }}
        </p>
    @else
        <div class="space-y-4">
            @foreach ($posts as $post)
                <x-card>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <a href="{{ route('alumkit.posts.public.show', $post) }}" class="hover:text-indigo-600">
                            {{ $post->title }}
                        </a>
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('alumkit::post.author') }}: {{ $post->user->name }} &middot; {{ $post->published_at->format('M j, Y') }}
                    </p>
                </x-card>
            @endforeach
        </div>
    @endif
@endsection
