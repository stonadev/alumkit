@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('alumkit::activity_log.pagination') }}" class="mt-10 flex items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="btn-secondary pointer-events-none opacity-40" aria-disabled="true">
                <span aria-hidden="true">←</span> {{ __('alumkit::activity_log.newer') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-secondary">
                <span aria-hidden="true">←</span> {{ __('alumkit::activity_log.newer') }}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-secondary">
                {{ __('alumkit::activity_log.older') }} <span aria-hidden="true">→</span>
            </a>
        @else
            <span class="btn-secondary pointer-events-none opacity-40" aria-disabled="true">
                {{ __('alumkit::activity_log.older') }} <span aria-hidden="true">→</span>
            </span>
        @endif
    </nav>
@endif
