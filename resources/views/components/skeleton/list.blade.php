{{--
    Skeleton danh sách dòng khi đang tải (DESIGN.md §9 Skeleton): khung giống dòng mini-cart / gợi ý tìm kiếm
    (ảnh 4:5 rộng 64px + 2 dòng chữ + giá). Mảng phẳng, không gradient/shimmer.
--}}
@props(['count' => 3])

<div {{ $attributes->class(['skeleton-list']) }} aria-busy="true">
    <span class="visually-hidden" role="status">Đang tải…</span>
    @foreach (range(1, $count) as $i)
        <div class="skeleton skeleton-row" aria-hidden="true">
            <span class="skeleton-row__thumb"></span>
            <span class="skeleton-row__text">
                <span class="skeleton__bar skeleton-row__name"></span>
                <span class="skeleton__bar skeleton__bar--small skeleton-row__meta"></span>
            </span>
            <span class="skeleton__bar skeleton-row__price"></span>
        </div>
    @endforeach
</div>
