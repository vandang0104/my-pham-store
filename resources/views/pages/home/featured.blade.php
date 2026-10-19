{{--
    2. Sản phẩm nổi bật "Đĩa vuông, chữ thưa": trái ảnh vuông lớn, phải hàng tab chữ (tên danh mục) + tên + mô tả
    + hai ô hành động. Không JS: tab là link tới trang sản phẩm, chỉ hiện sản phẩm đầu. Có JS: tab đổi tại chỗ (home.js).
--}}
@if ($featured->isNotEmpty())
    <section class="home-featured section" aria-labelledby="featured-title">
        <div class="container">
            <x-section-heading id="featured-title" title="Sản phẩm nổi bật" lead="Những sản phẩm Mộc Lam chọn giới thiệu, mỗi nhóm một món." data-reveal />

            <div class="featured" data-tabs data-reveal>
                <div class="featured__media">
                    @foreach ($featured as $i => $product)
                        <div class="featured__image" data-tab-media @if ($i > 0) hidden @endif>
                            @if ($product->primary_image)
                                <img src="{{ $product->primary_image->url }}" alt="{{ $product->name }}" width="800" height="1000" loading="lazy" decoding="async">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="featured__body">
                    <div class="featured__tabs" data-tablist aria-label="Sản phẩm nổi bật theo nhóm">
                        @foreach ($featured as $i => $product)
                            <a href="{{ url('/san-pham/'.$product->slug) }}" id="featured-tab-{{ $product->id }}" class="featured__tab"
                                data-tab aria-controls="featured-panel-{{ $product->id }}" @if ($i === 0) aria-current="true" @endif>{{ $product->category->name }}</a>
                        @endforeach
                    </div>

                    @foreach ($featured as $i => $product)
                        <article id="featured-panel-{{ $product->id }}" class="featured__panel" data-tab-panel aria-labelledby="featured-name-{{ $product->id }}" @if ($i > 0) hidden @endif>
                            <h3 id="featured-name-{{ $product->id }}" class="t-title featured__name">{{ $product->name }}</h3>
                            <p class="featured__brand">{{ collect([$product->brand?->name, $product->volume])->filter()->implode(' · ') }}</p>
                            @if ($product->short_description)
                                <p class="featured__text">{{ $product->short_description }}</p>
                            @endif
                            <div class="featured__meta">
                                <x-price :product="$product" size="lg" />
                                <x-rating :value="$product->rating_avg" :count="$product->rating_count" />
                            </div>

                            <div class="featured__actions">
                                <form method="POST" action="{{ url('/gio-hang') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100" @disabled($product->stock <= 0)>
                                        {{ $product->stock > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}
                                    </button>
                                </form>
                                <a href="{{ url('/san-pham/'.$product->slug) }}" class="btn btn-text featured__detail">Xem chi tiết</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
