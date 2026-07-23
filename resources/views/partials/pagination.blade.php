@if ($paginator->hasPages())
    <nav class="flex-between" style="padding: 10px 0; gap: 10px;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn-secondary btn-sm" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary btn-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex-h" style="gap: 5px;">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="color: var(--text3); padding: 0 5px;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-primary btn-sm" style="min-width: 32px; justify-content: center; background: var(--accent); border-radius: 6px;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="btn-secondary btn-sm" style="min-width: 32px; justify-content: center; border-radius: 6px;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary btn-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="btn-secondary btn-sm" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
