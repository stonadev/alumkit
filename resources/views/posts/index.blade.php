@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::post.my_posts') }}
        </h1>

        <a href="{{ route('alumkit.posts.create') }}">
            <x-button :text="__('alumkit::post.new_post')" />
        </a>
    </div>

    <x-card>
        @if ($posts->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::post.no_posts') }}
            </p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">{{ __('alumkit::post.title') }}</th>
                        <th class="text-left py-3 px-4">{{ __('alumkit::post.status') }}</th>
                        <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">
                                {{ $post->title }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($post->isPublished())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('alumkit::post.published') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('alumkit::post.draft') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.posts.edit', $post) }}" class="text-navy hover:text-gold mr-3">
                                    {{ __('alumkit::dashboard.edit') }}
                                </a>

                                <form method="POST" action="{{ route('alumkit.posts.destroy', $post) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        {{ __('alumkit::dashboard.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
@endsection
