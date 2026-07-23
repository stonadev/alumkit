<div class="max-w-lg w-full mx-auto">
    <x-card {{ $attributes->class('space-y-6') }}>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h1>
            @if (isset($subtitle))
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <x-errors />

        {{ $slot }}

        @if (isset($footer))
            <div class="text-center border-t border-gray-200 dark:border-gray-700 pt-4">
                {{ $footer }}
            </div>
        @endif
    </x-card>
</div>
