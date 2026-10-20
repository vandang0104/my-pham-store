{{--
    Hero "Thẻ nhãn có sọc cạnh ảnh": trái tấm nhãn (dải sọc, wordmark, h1 duy nhất, câu dẫn, nút, 3 link),
    phải ảnh banner tĩnh (banner home_hero đầu tiên). Không có banner → mảng ngọc nhạt phẳng + tiêu đề mặc định.
--}}
<section class="home-hero" aria-labelledby="home-title">
    <div class="home-hero__inner">
        <div class="home-hero__card">
            <div class="home-hero__stripe" aria-hidden="true"></div>
            <div class="home-hero__body">
                <x-wordmark class="home-hero__wordmark" />
                <h1 id="home-title" class="home-hero__title">{{ $banner?->title ?? 'Mỹ phẩm thảo mộc Việt' }}</h1>
                <p class="home-hero__lead">Chăm sóc da mặt từ nguyên liệu Đà Lạt, Bảo Lộc, Hưng Yên — ghi rõ thành phần và nơi trồng.</p>
                <a href="{{ url($banner?->link ?: '/san-pham') }}" class="btn btn-primary">{{ $banner ? 'Xem bộ sưu tập' : 'Xem sản phẩm' }}</a>

                <ul class="home-hero__links">
                    <li><a href="{{ url('/danh-muc/duong-da') }}" class="link-nav">Dưỡng da</a></li>
                    <li><a href="{{ url('/san-pham?khuyen-mai=1') }}" class="link-nav">Đang ưu đãi</a></li>
                    <li><a href="#gioi-thieu" class="link-nav">Về Mộc Lam</a></li>
                </ul>
            </div>
        </div>

        <div @class(['home-hero__media', 'home-hero__media--empty' => ! $banner])>
            @if ($banner)
                <picture>
                    @if ($banner->desktop_url)
                        <source media="(min-width: 992px)" srcset="{{ $banner->desktop_url }}" width="1920" height="720">
                    @endif
                    {{-- Ảnh hero là LCP: tải ngay, ưu tiên cao --}}
                    <img src="{{ $banner->mobile_url }}" alt="{{ $banner->title }}"
                        width="800" height="1000" fetchpriority="high" decoding="async">
                </picture>
            @endif
        </div>
    </div>
</section>
