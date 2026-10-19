{{-- Sản phẩm nổi bật: lưới tối đa 8 sản phẩm is_featured. Không có → ẩn khối. --}}
@if ($featured->isNotEmpty())
    <section class="home-featured section" aria-labelledby="featured-title">
        <div class="container">
            <x-section-heading id="featured-title" title="Sản phẩm nổi bật" lead="Những sản phẩm Mộc Lam chọn giới thiệu."
                :link-url="url('/san-pham')" link-label="Xem tất cả" />
            <div class="product-grid">
                @foreach ($featured as $i => $product)
                    <x-product-card :product="$product" :index="$i" />
                @endforeach
            </div>
        </div>
    </section>
@endif
