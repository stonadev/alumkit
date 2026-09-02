@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::committee.edit_member') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.committee.update', $member) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4" x-data="{ memberType: '{{ $member->user_id ? 'registered' : 'non_registered' }}' }">
                <x-alumkit::select name="position_id" :label="__('alumkit::committee.position')"
                    :options="$positions->pluck('name', 'id')->toArray()" :value="$member->position_id" :placeholder="__('alumkit::committee.select_position')" required />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('alumkit::committee.member_type') }}</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="member_type" value="registered" x-model="memberType" class="text-navy focus:ring-gold/50">
                            {{ __('alumkit::committee.registered_user') }}
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="member_type" value="non_registered" x-model="memberType" class="text-navy focus:ring-gold/50">
                            {{ __('alumkit::committee.non_registered') }}
                        </label>
                    </div>
                </div>

                <div x-show="memberType === 'registered'" x-cloak>
                    <x-alumkit::user-search name="user_id" :label="__('alumkit::committee.search_members')" :value="$member->user_id ? (int) $member->user_id : null" />
                </div>

                <div x-show="memberType === 'non_registered'" x-cloak>
                    <x-input name="name" :label="__('alumkit::committee.member_name')" :value="$member->name" />

                    <div class="mt-4" x-data="{ photoPreview: null }">
                        <span class="dark:text-dark-400 mb-1 block text-sm font-semibold text-gray-600">{{ __('alumkit::committee.photo') }}</span>
                        <span role="button" tabindex="0"
                              class="flex h-40 w-40 cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-gray-100 focus-visible:ring-2 focus-visible:ring-gold/50"
                              x-on:click="$refs.photoInput.click()"
                              x-on:keydown.enter.prevent="$refs.photoInput.click()"
                              x-on:keydown.space.prevent="$refs.photoInput.click()">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                @if ($member->photoUrl())
                                    <img src="{{ $member->photoUrl() }}" alt="{{ __('alumkit::committee.photo') }}" class="h-full w-full object-cover">
                                @else
                                    <span class="flex flex-col items-center gap-1.5 text-sm text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        <span>{{ __('alumkit::committee.select_photo') }}</span>
                                    </span>
                                @endif
                            </template>
                        </span>
                        <input type="file" name="photo" accept="image/*" hidden x-ref="photoInput"
                               x-on:change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        @error('photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::committee.edit_member')" />
                <a href="{{ route('alumkit.committee.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
