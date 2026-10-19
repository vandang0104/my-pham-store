{{-- Đang ưu đãi: tối đa 8 sản phẩm giảm giá, sắp theo % giảm. Không có sản phẩm giảm giá → ẩn cả section. --}}
@if ($onSale->isNotEmpty())
    <section class="home-sale section" aria-labelledby="sale-title">
        <div class="container">
            <x-section-heading id="sale-title" title="Đang ưu đãi" lead="Sắp theo mức giảm nhiều nhất; giá đã gồm khuyến mãi."
                :link-url="url('/san-pham?khuyen-mai=1')" link-label="Xem tất cả" />
            <div class="product-grid">
                @foreach ($onSale as $i => $product)
                    <x-product-card :product="$product" :index="$i" />
                @endforeach
            </div>
        </div>
    </section>
@endif
