{{--
    Link nằm ở tên sản phẩm và phủ cả thẻ bằng ::after (stretched link).
    Nút tim và form thêm giỏ là phần tử anh em của link (không lồng trong <a>), nằm trên lớp phủ bằng z-index.
--}}
<article {{ $attributes->merge(['class' => 'product-card product-card--tone-'.$tone.($outOfStock ? ' is-out-of-stock' : '')]) }}>
    <div class="product-card__media">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" width="800" height="1000" loading="lazy" decoding="async">
            @if ($isPlaceholder)
                {{-- Ảnh giữ chỗ không chứa chữ (SVG trong <img> không dùng được web font): vẽ tên bằng Cormorant --}}
                <span class="product-card__label" aria-hidden="true">{{ $product->name }}</span>
            @endif
        @else
            <div class="product-card__placeholder" role="img" aria-label="{{ $product->name }}">
                <span>{{ $product->name }}</span>
            </div>
        @endif

        @if ($product->is_on_sale)
            <span class="product-card__discount" aria-hidden="true">−{{ $product->discount_percent }}%</span>
        @endif

        <button type="button" class="product-card__wish" aria-pressed="false" aria-label="Thêm {{ $product->name }} vào danh sách yêu thích">
            <x-icon name="heart" :size="20" />
        </button>

        <form method="POST" action="{{ url('/gio-hang') }}" class="product-card__cart">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="product-card__add" @disabled($outOfStock)
                aria-label="{{ $outOfStock ? $product->name.' đã hết hàng' : 'Thêm '.$product->name.' vào giỏ' }}">
                <x-icon name="bag" :size="20" />
                <span class="product-card__add-text" aria-hidden="true">{{ $outOfStock ? 'Hết hàng' : 'Thêm vào giỏ' }}</span>
            </button>
        </form>
    </div>

    <div class="product-card__tag">
        @if ($product->brand)
            <p class="product-card__brand">{{ $product->brand->name }}</p>
        @endif
        <{{ $headingTag }} class="product-card__name">
            <a href="{{ $url }}" class="product-card__link">{{ $product->name }}</a>
        </{{ $headingTag }}>
        <div class="product-card__row">
            <p class="product-card__meta">{{ $meta }}</p>
            <x-price :product="$product" size="sm" />
        </div>
        @if ($outOfStock)
            <p class="product-card__stock">Hết hàng</p>
        @endif
    </div>
</article>
