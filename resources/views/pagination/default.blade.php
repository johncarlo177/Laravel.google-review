@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="margin-top: 20px;">
        <ul style="display: flex; list-style: none; padding: 0; gap: 10px; justify-content: center; align-items: center;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li style="padding: 8px 12px; color: #999; cursor: not-allowed;">
                    <span>« Previous</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                        « Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li style="padding: 8px 12px; color: #666;">
                        <span>{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li style="padding: 8px 12px; background: #4285f4; color: white; border-radius: 4px;">
                                <span>{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" style="padding: 8px 12px; text-decoration: none; color: #4285f4; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                        Next »
                    </a>
                </li>
            @else
                <li style="padding: 8px 12px; color: #999; cursor: not-allowed;">
                    <span>Next »</span>
                </li>
            @endif
        </ul>

        <div style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>
    </nav>
@endif

