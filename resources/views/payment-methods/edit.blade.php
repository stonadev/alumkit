@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.edit_payment_method'))

@section('content')
    <h1 class="text-2xl font-bold text-navy mb-6">
        {{ __('alumkit::membership.edit_payment_method') }}
    </h1>

    <x-card>
        <form method="POST" action="{{ route('alumkit.payment-methods.update', $paymentMethod) }}">
            @csrf
            @method('PUT')

            @include('alumkit::payment-methods.form', ['method' => $paymentMethod])

            <div class="mt-6 flex items-center gap-4">
                <x-button type="submit" :text="__('alumkit::membership.method_updated')" />
                <a href="{{ route('alumkit.payment-methods.index') }}" class="text-gray-600 hover:text-navy">
                    {{ __('alumkit::dashboard.back_to_dashboard') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
