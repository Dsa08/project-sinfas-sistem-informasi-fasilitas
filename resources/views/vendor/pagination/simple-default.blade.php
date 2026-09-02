@if ($paginator->hasPages())
    <nav>
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn--disabled">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" style="text-decoration: none;">Prev</a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-btn pagination-btn--disabled">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn pagination-btn--active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn" style="text-decoration: none;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" style="text-decoration: none;">Next</a>
            @else
                <span class="pagination-btn pagination-btn--disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
