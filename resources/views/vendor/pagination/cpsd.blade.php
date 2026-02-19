@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-2">
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-xs font-semibold uppercase rounded cursor-not-allowed"
                style="background:#9ca3af !important; border:1px solid #6b7280 !important; color:#111827 !important; text-decoration:none !important; opacity:1 !important;">Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-4 py-2 text-xs font-semibold uppercase rounded"
                style="background:#1f2937 !important; border:1px solid #111827 !important; color:#ffffff !important; text-decoration:none !important; opacity:1 !important;">Prev</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-sm text-gray-500">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            class="min-w-8 text-center px-2 py-2 text-sm font-semibold rounded"
                            style="background:#0aa03a !important; border:1px solid #087c2d !important; color:#ffffff !important; text-decoration:none !important; opacity:1 !important;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="min-w-8 text-center px-2 py-2 text-sm rounded"
                            style="background:#ffffff !important; border:1px solid #d1d5db !important; color:#111827 !important; text-decoration:none !important; opacity:1 !important;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-4 py-2 text-xs font-semibold uppercase rounded"
                style="background:#1f2937 !important; border:1px solid #111827 !important; color:#ffffff !important; text-decoration:none !important; opacity:1 !important;">Next</a>
        @else
            <span class="px-4 py-2 text-xs font-semibold uppercase rounded cursor-not-allowed"
                style="background:#9ca3af !important; border:1px solid #6b7280 !important; color:#111827 !important; text-decoration:none !important; opacity:1 !important;">Next</span>
        @endif
    </nav>
@endif
