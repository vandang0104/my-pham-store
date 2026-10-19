{{--
    1. Hero "Thẻ nhãn có sọc cạnh ảnh": trái tấm nhãn nền lụa (dải sọc, wordmark, h1 duy nhất, câu dẫn, nút, 3 link),
    phải ảnh banner (home_hero). Từ 2 banner: crossfade chậm (resources/js/home.js), tiêu đề/link banner đang hiện
    dùng cho h1 và nút. Không có banner: mảng ngọc nhạt phẳng + tiêu đề mặc định.
--}}
@php($first = $banners[0] ?? null)

<section class="home-hero" aria-labelledby="home-title" data-hero>
    <div class="home-hero__inner">
        <div class="home-hero__card">
            <div class="home-hero__stripe" aria-hidden="true"></div>
            <div class="home-hero__body">
                <x-wordmark class="home-hero__wordmark" />
                <h1 id="home-title" class="home-hero__title" data-hero-title>{{ $first['title'] ?? 'Mỹ phẩm thảo mộc Việt' }}</h1>
                <p class="home-hero__lead">Chăm sóc da, trang điểm và nước hoa từ nguyên liệu Đà Lạt, Hội An, Đắk Lắk — ghi rõ thành phần và nơi trồng.</p>
                <a href="{{ $first['url'] ?? url('/san-pham') }}" class="btn btn-primary home-hero__action" data-hero-link>{{ $first ? 'Xem bộ sưu tập' : 'Xem sản phẩm' }}</a>

                <div class="home-hero__foot">
                    <ul class="home-hero__links">
                        <li><a href="{{ url('/danh-muc/cham-soc-da-mat') }}" class="link-nav">Chăm sóc da mặt</a></li>
                        <li><a href="{{ url('/san-pham?khuyen-mai=1') }}" class="link-nav">Đang ưu đãi</a></li>
                        <li><a href="#cau-chuyen" class="link-nav">Câu chuyện Mộc Lam</a></li>
                    </ul>
                    @if (count($banners) > 1)
                        <button type="button" class="btn btn-text home-hero__pause" data-hero-pause aria-pressed="false">Dừng chuyển ảnh</button>
                    @endif
                </div>
            </div>
        </div>

        <div @class(['home-hero__media', 'home-hero__media--empty' => ! $first])>
            @foreach ($banners as $i => $banner)
                <picture @class(['home-hero__slide', 'is-active' => $i === 0]) data-hero-slide data-title="{{ $banner['title'] }}" data-url="{{ $banner['url'] }}" @if ($i > 0) aria-hidden="true" @endif>
                    @if ($banner['desktop'])
                        <source media="(min-width: 992px)" srcset="{{ $banner['desktop'] }}" width="1920" height="720">
                    @endif
                    {{-- Ảnh đầu là LCP: tải ngay, ưu tiên cao; các ảnh sau lazy --}}
                    <img src="{{ $banner['mobile'] }}" alt="{{ $banner['title'] }}" width="800" height="1000"
                        @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                </picture>
            @endforeach
        </div>
    </div>
</section>
