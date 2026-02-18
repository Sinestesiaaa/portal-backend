@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-xs font-semibold uppercase rounded bg-gray-500 text-white/70 cursor-not-allowed">Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-4 py-2 text-xs font-semibold uppercase rounded bg-gray-600 text-white hover:bg-gray-700">Prev</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-sm text-gray-500">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            class="min-w-8 text-center px-2 py-2 text-sm font-semibold rounded bg-[#0AA03A] text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="min-w-8 text-center px-2 py-2 text-sm rounded text-gray-800 hover:bg-gray-100">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-4 py-2 text-xs font-semibold uppercase rounded bg-gray-600 text-white hover:bg-gray-700">Next</a>
        @else
            <span class="px-4 py-2 text-xs font-semibold uppercase rounded bg-gray-500 text-white/70 cursor-not-allowed">Next</span>
        @endif
    </nav>
@endif
