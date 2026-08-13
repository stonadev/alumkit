<div class="max-w-lg w-full mx-auto">
    <x-card {{ $attributes->class('space-y-6') }}>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-navy">
                {{ $title }}
            </h1>
            @if (isset($subtitle))
                <p class="mt-2 text-sm text-gray-600">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <x-errors />

        {{ $slot }}

        @if (isset($footer))
            <div class="text-center border-t border-gray-200 pt-4">
                {{ $footer }}
            </div>
        @endif
    </x-card>
</div>
