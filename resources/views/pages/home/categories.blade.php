{{--
    3. Danh mục "Chữ khổng lồ, các thanh màu": một từ rất lớn (line-height 1.1 vì dấu tiếng Việt) + 8 thanh màu đặc
    (danh mục cha) mở tại chỗ bằng <details>: danh mục con + số sản phẩm. Dữ liệu cache, xem config/home.php.
--}}
@if ($categories !== [])
    <section class="home-categories section" aria-labelledby="categories-title">
        <div class="container">
            <div class="home-categories__head" data-reveal>
                <h2 id="categories-title" class="home-categories__word">Danh mục</h2>
                <p class="home-categories__lead">
                    {{ count($categories) }} nhóm, {{ $categoryTotals['children'] }} danh mục con, {{ $categoryTotals['products'] }} sản phẩm đang bán. Mở một thanh để xem bên trong.
                </p>
            </div>

            <div class="cat-bars">
                @foreach ($categories as $category)
                    <details class="cat-bar cat-bar--tone-{{ $category['tone'] }}" data-reveal>
                        <summary class="cat-bar__summary">
                            <span class="cat-bar__name">{{ $category['name'] }}</span>
                            <span class="cat-bar__count">{{ $category['count'] }} sản phẩm</span>
                            <x-icon name="chevron-down" :size="20" class="cat-bar__chevron" />
                        </summary>
                        <div class="cat-bar__body">
                            @if ($category['children'] !== [])
                                <ul class="cat-bar__list">
                                    @foreach ($category['children'] as $child)
                                        <li>
                                            <a href="{{ $child['url'] }}" class="cat-bar__link">{{ $child['name'] }}</a>
                                            <span class="cat-bar__child-count">{{ $child['count'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <a href="{{ $category['url'] }}" class="cat-bar__all link-nav">Xem tất cả {{ mb_strtolower($category['name']) }}
                                <x-icon name="arrow-right" :size="20" /></a>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
