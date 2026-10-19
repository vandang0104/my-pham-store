{{--
    Skeleton lưới thẻ sản phẩm khi đang tải (DESIGN.md §9 Skeleton): cùng khung với <x-product-card>
    (ảnh 4:5 + dải nhãn 3 dòng) để trang không nhảy. Mảng phẳng, không gradient/shimmer.
--}}
@props(['count' => 4])

<div {{ $attributes->class(['product-grid']) }} aria-busy="true">
    <span class="visually-hidden" role="status">Đang tải sản phẩm…</span>
    @foreach (range(1, $count) as $i)
        <div class="skeleton skeleton-card" aria-hidden="true">
            <div class="skeleton-card__media"></div>
            <div class="skeleton-card__tag">
                <span class="skeleton__bar skeleton__bar--small skeleton-card__brand"></span>
                <span class="skeleton__bar skeleton-card__name"></span>
                <span class="skeleton__bar skeleton-card__name skeleton-card__name--short"></span>
                <span class="skeleton-card__row">
                    <span class="skeleton__bar skeleton__bar--small skeleton-card__meta"></span>
                    <span class="skeleton__bar skeleton-card__price"></span>
                </span>
            </div>
        </div>
    @endforeach
</div>
