{{--
    Header (DESIGN.md §9). $navigation (menu đã cache) và $cartCount do View Composer cấp.
    Desktop (≥ 1200px): wordmark · 5 nhóm có mega menu · tìm kiếm, tài khoản, túi.
    Điện thoại: nút menu (ngăn kéo trái) · wordmark · tìm kiếm, túi.
--}}
<header class="site-header" data-site-header>
    <div class="container site-header__bar">
        <button type="button" class="icon-btn d-xl-none" data-bs-toggle="offcanvas" data-bs-target="#menu-drawer" aria-controls="menu-drawer" aria-label="Mở menu">
            <x-icon name="menu" />
        </button>

        <a href="{{ route('home') }}" class="site-header__brand">
            <x-wordmark />
        </a>

        <nav class="site-nav d-none d-xl-block" aria-label="Danh mục chính">
            <ul class="site-nav__list">
                @foreach ($navigation as $i => $group)
                    <li class="site-nav__item" data-mega>
                        <button type="button" class="site-nav__trigger" aria-expanded="false" aria-controls="mega-{{ $i }}" data-mega-trigger>
                            {{ $group['label'] }}
                        </button>
                        <div class="mega" id="mega-{{ $i }}" data-mega-panel hidden>
                            <div class="container mega__inner">
                                <div class="mega__columns">
                                    @foreach ($group['columns'] as $column)
                                        <div class="mega__column">
                                            <a href="{{ $column['url'] }}" class="mega__title link-nav">{{ $column['title'] }}</a>
                                            <ul class="mega__links">
                                                @foreach ($column['links'] as $link)
                                                    <li><a href="{{ $link['url'] }}" class="link-nav">{{ $link['label'] }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($group['image'])
                                    <a href="{{ $group['url'] }}" class="mega__media" tabindex="-1" aria-hidden="true">
                                        <img src="{{ $group['image'] }}" alt="" width="600" height="600" loading="lazy" decoding="async">
                                    </a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="site-header__actions">
            <button type="button" class="icon-btn" data-bs-toggle="offcanvas" data-bs-target="#search-panel" aria-controls="search-panel" aria-label="Tìm kiếm">
                <x-icon name="search" />
            </button>

            @auth
                <div class="dropdown d-none d-xl-block">
                    <button type="button" class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tài khoản của {{ auth()->user()->name }}">
                        <x-icon name="user" />
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end account-menu">
                        <li><p class="account-menu__hello">Xin chào, {{ auth()->user()->name }}</p></li>
                        <li><a class="dropdown-item" href="{{ url('/tai-khoan') }}">Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item" href="{{ url('/tai-khoan/don-hang') }}">Đơn hàng của tôi</a></li>
                        @if (auth()->user()->isBackOffice())
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Trang quản trị</a></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="icon-btn d-none d-xl-inline-flex" aria-label="Đăng nhập">
                    <x-icon name="user" />
                </a>
            @endauth

            <a href="{{ url('/gio-hang') }}" class="icon-btn icon-btn--cart" aria-label="Giỏ hàng">
                <x-icon name="bag" />
                <x-cart-badge :count="$cartCount" />
            </a>
        </div>
    </div>

    {{-- Tìm kiếm: chỉ dựng giao diện; gợi ý tự động làm ở bước 4.3 --}}
    <div class="offcanvas offcanvas-top search-panel" id="search-panel" tabindex="-1" aria-labelledby="search-panel-title" data-search-panel>
        <div class="container search-panel__inner">
            <h2 id="search-panel-title" class="visually-hidden">Tìm kiếm sản phẩm</h2>
            <form action="{{ url('/san-pham') }}" method="GET" role="search" class="search-form">
                <label for="search-q" class="visually-hidden">Tìm sản phẩm, thương hiệu</label>
                <x-icon name="search" class="search-form__icon" />
                <input id="search-q" name="q" type="search" class="search-form__input" placeholder="Tìm serum, kem chống nắng, thương hiệu…" autocomplete="off" enterkeyhint="search">
                <button type="submit" class="btn btn-primary search-form__submit">Tìm</button>
            </form>
            <button type="button" class="icon-btn search-panel__close" data-bs-dismiss="offcanvas" aria-label="Đóng tìm kiếm">
                <x-icon name="close" />
            </button>
        </div>
    </div>

    {{-- Menu điện thoại: ngăn kéo trái --}}
    <div class="offcanvas offcanvas-start menu-drawer" id="menu-drawer" tabindex="-1" aria-labelledby="menu-drawer-title">
        <div class="menu-drawer__head">
            <p id="menu-drawer-title" class="menu-drawer__title"><x-wordmark /></p>
            <button type="button" class="icon-btn" data-bs-dismiss="offcanvas" aria-label="Đóng menu">
                <x-icon name="close" />
            </button>
        </div>
        <div class="offcanvas-body menu-drawer__body">
            <nav aria-label="Danh mục chính">
                @foreach ($navigation as $group)
                    <details class="drawer-group">
                        <summary class="drawer-group__summary">
                            {{ $group['label'] }}
                            <x-icon name="chevron-down" :size="20" class="drawer-group__chevron" />
                        </summary>
                        <div class="drawer-group__body">
                            @foreach ($group['columns'] as $column)
                                <a href="{{ $column['url'] }}" class="drawer-group__title">{{ $column['title'] }}</a>
                                <ul class="drawer-group__links">
                                    @foreach ($column['links'] as $link)
                                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>

            <ul class="drawer-links">
                @auth
                    <li><a href="{{ url('/tai-khoan') }}">Tài khoản của tôi</a></li>
                    <li><a href="{{ url('/tai-khoan/don-hang') }}">Đơn hàng của tôi</a></li>
                    @if (auth()->user()->isBackOffice())
                        <li><a href="{{ route('admin.dashboard') }}">Trang quản trị</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="drawer-links__button">Đăng xuất</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                    <li><a href="{{ route('register') }}">Đăng ký</a></li>
                @endauth
                <li><a href="{{ url('/tra-cuu-don-hang') }}">Tra cứu đơn hàng</a></li>
                <li><a href="{{ url('/tin-tuc') }}">Tin tức</a></li>
            </ul>
        </div>
    </div>
</header>
