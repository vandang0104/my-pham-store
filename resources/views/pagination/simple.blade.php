{{-- Phân trang đơn giản (simplePaginate): chỉ "Trước" / "Sau" --}}
@if ($paginator->hasPages())
    <nav class="pagination-nav" aria-label="Phân trang">
        <div class="pager">
            @if ($paginator->onFirstPage())
                <span class="pager__link is-disabled" aria-disabled="true">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pager__link link-nav" rel="prev">{{ __('pagination.previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pager__link link-nav" rel="next">{{ __('pagination.next') }}</a>
            @else
                <span class="pager__link is-disabled" aria-disabled="true">{{ __('pagination.next') }}</span>
            @endif
        </div>
    </nav>
@endif
