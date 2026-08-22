@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">Pages</h1>

        <a href="{{ route('alumkit.pages.create') }}">
            <x-button text="Create Page" />
        </a>
    </div>

    <x-card>
        @if ($pages->isEmpty())
            <p class="text-gray-600">No pages yet.</p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Title</th>
                        <th class="text-left py-3 px-4">Slug</th>
                        <th class="text-left py-3 px-4">Status</th>
                        <th class="text-right py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr class="border-b">
                            <td class="py-3 px-4 font-medium">{{ $page->title }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $page->slug }}</td>
                            <td class="py-3 px-4">
                                @if ($page->is_published)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('alumkit.pages.edit', $page) }}" class="text-navy hover:text-gold mr-3">
                                    Edit
                                </a>
                                <a href="{{ route('alumkit.pages.content.edit', $page) }}" class="text-navy hover:text-gold mr-3">
                                    Content
                                </a>
                                <form method="POST" action="{{ route('alumkit.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Delete
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
