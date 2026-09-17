@extends('alumkit::layouts.dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::committee.new_member') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.committee.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-4" x-data="{ memberType: '{{ old('user_id') ? 'registered' : 'non_registered' }}' }">
                <x-alumkit::select name="position_id" :label="__('alumkit::committee.position')"
                    :options="$positions->pluck('name', 'id')->toArray()" :value="old('position_id')" :placeholder="__('alumkit::committee.select_position')" required />

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
                    <x-alumkit::user-search name="user_id" :label="__('alumkit::committee.search_members')" :value="old('user_id') ? (int) old('user_id') : null" />
                </div>

                <div x-show="memberType === 'non_registered'" x-cloak>
                    <x-input name="name" :label="__('alumkit::committee.member_name')" :value="old('name')" />

                    <div class="mt-4">
                        <x-alumkit::photo-cropper name="photo" :choose-label="__('alumkit::committee.select_photo')">
                            <x-slot:label>
                                <span class="dark:text-dark-400 mb-1 block text-sm font-semibold text-gray-600">{{ __('alumkit::committee.photo') }}</span>
                            </x-slot:label>
                        </x-alumkit::photo-cropper>
                    </div>
                </div>

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::committee.new_member')" />
                <a href="{{ route('alumkit.committee.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
