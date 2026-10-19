{{--
    Phân trang mặc định (Paginator::defaultView) theo DESIGN.md §9.
    Điện thoại: "Trước · Trang 2/5 · Sau"; từ 768px: số trang, trang hiện tại gạch chân men ngọc.
    Bộ lọc được giữ khi controller gọi ->withQueryString().
--}}
@if ($paginator->hasPages())
    <nav class="pagination-nav" aria-label="Phân trang">
        <div class="pager d-md-none">
            @if ($paginator->onFirstPage())
                <span class="pager__link is-disabled" aria-disabled="true">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pager__link link-nav" rel="prev">{{ __('pagination.previous') }}</a>
            @endif

            <span class="pager__status">Trang {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pager__link link-nav" rel="next">{{ __('pagination.next') }}</a>
            @else
                <span class="pager__link is-disabled" aria-disabled="true">{{ __('pagination.next') }}</span>
            @endif
        </div>

        <ul class="pagination d-none d-md-flex">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ __('pagination.previous') }}</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('pagination.previous') }}</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('pagination.next') }}</a></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ __('pagination.next') }}</span></li>
            @endif
        </ul>
    </nav>
@endif
