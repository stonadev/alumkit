@extends('alumkit::layouts.dashboard')

@section('title', __('alumkit::membership.plans'))

@section('content')
    <div class="space-y-8">
        <section>
            <h1 class="font-serif text-3xl font-semibold text-navy">
                {{ __('alumkit::membership.plans') }}
            </h1>
            <p class="mt-3 max-w-2xl leading-7 text-on-surface-variant">
                {{ __('alumkit::membership.select_plan') }}
            </p>
        </section>

        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($plans as $plan)
                <div class="card flex flex-col p-6">
                    <h2 class="mt-2 font-serif text-xl font-semibold text-navy">{{ $plan->name }}</h2>
                    <p class="mt-1 text-sm text-on-surface-variant">{{ $plan->termLabel() }}</p>

                    <div class="mt-4">
                        <p class="font-serif text-3xl font-semibold text-navy">
                            {{ \Alumkit\Alumkit\Facades\Alumkit::formatMoney($plan->price) }}
                        </p>
                    </div>

                    @if ($plan->description)
                        <p class="mt-4 text-sm leading-6 text-on-surface-variant">{{ $plan->description }}</p>
                    @endif

                    @if ($plan->features)
                        <ul class="mt-4 space-y-1.5 text-sm text-on-surface-variant">
                            @foreach ($plan->features as $key => $value)
                                <li class="flex items-center gap-2">
                                    <span class="text-gold">✓</span>
                                    <span>{{ $key }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-6 flex flex-col gap-3 pt-2">
                        <a href="{{ route('alumkit.membership.payments.create') }}" class="btn-primary w-full text-center">
                            {{ __('alumkit::membership.choose') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="card col-span-full px-6 py-8 text-sm text-on-surface-variant">
                    {{ __('alumkit::membership.no_plans') }}
                </div>
            @endforelse
        </section>
    </div>
@endsection
