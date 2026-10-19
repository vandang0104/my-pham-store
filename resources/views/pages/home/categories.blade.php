{{-- Danh mục: lưới danh mục cha, mỗi ô liệt kê danh mục con kèm số sản phẩm đang bán --}}
@if ($categories->isNotEmpty())
    <section class="home-categories section" aria-labelledby="categories-title">
        <div class="container">
            <x-section-heading id="categories-title" title="Danh mục" lead="Chọn nhóm sản phẩm bạn cần." />
            <div class="home-categories__grid">
                @foreach ($categories as $category)
                    <div class="home-category">
                        <h3 class="home-category__title"><a href="{{ url('/danh-muc/'.$category->slug) }}">{{ $category->name }}</a></h3>
                        @if ($category->children->isNotEmpty())
                            <ul class="home-category__list">
                                @foreach ($category->children as $child)
                                    <li>
                                        <a href="{{ url('/danh-muc/'.$child->slug) }}">{{ $child->name }}</a>
                                        <span class="home-category__count">{{ $child->products_count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
