@extends('alumkit::layouts.dashboard')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-navy">
            {{ __('alumkit::committee.committee') }}
        </h1>

        <div class="flex items-center gap-4">
            <a href="{{ route('alumkit.positions.index') }}" class="text-navy hover:text-gold">
                {{ __('alumkit::committee.manage_positions') }}
            </a>
            <a href="{{ route('alumkit.committee.create') }}">
                <x-button :text="__('alumkit::committee.new_member')" />
            </a>
        </div>
    </div>

    <x-card>
        @if ($members->isEmpty())
            <p class="text-gray-600">
                {{ __('alumkit::committee.no_members') }}
            </p>
        @else
            <p class="text-sm text-gray-500 mb-4">{{ __('alumkit::committee.drag_to_reorder') }}</p>

            <div wire:ignore>
                <table class="w-full" id="committee-table">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 w-8"></th>
                            <th class="text-left py-3 px-4">{{ __('alumkit::committee.position') }}</th>
                            <th class="text-left py-3 px-4">{{ __('alumkit::committee.members') }}</th>
                            <th class="text-left py-3 px-4">{{ __('alumkit::committee.photo') }}</th>
                            <th class="text-right py-3 px-4">{{ __('alumkit::dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="committee-tbody" x-data x-init="
                        import('{{ url('alumkit/style/alumkit-sortable.esm.js') }}').then(function(m) {
                            new m.default($el, {
                                animation: 150,
                                handle: '.drag-handle',
                                onEnd: function(evt) {
                                    var ids = Array.from($el.querySelectorAll('tr')).map(function(row) { return row.dataset.id; });
                                    $wire.reorder(ids);
                                }
                            });
                        });
                    ">
                        @foreach ($members as $member)
                            <tr class="border-b" data-id="{{ $member->id }}">
                                <td class="py-3 px-4">
                                    <span class="drag-handle cursor-grab text-gray-400 hover:text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                                        </svg>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    {{ $member->position?->name ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-medium">
                                    {{ $member->displayName() }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($member->photoUrl())
                                        <img src="{{ $member->photoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('alumkit.committee.edit', $member) }}" class="text-navy hover:text-gold mr-3">
                                        {{ __('alumkit::dashboard.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('alumkit.committee.destroy', $member) }}" class="inline" onsubmit="return confirm('{{ __('alumkit::dashboard.confirm_delete') }}')">
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
            </div>
        @endif
    </x-card>
@endsection
