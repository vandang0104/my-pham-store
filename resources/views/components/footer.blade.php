{{--
    Footer — "mặt sau của tấm nhãn" (DESIGN.md §9): nền sơn mài, dải sọc nhãn ở mép trên,
    chữ thương hiệu dựng khối toàn chiều rộng, 3 cột link, liên hệ, bản quyền.
    Thông tin liên hệ là dữ liệu MẪU viết thẳng ở đây (màn cài đặt đã cắt khỏi phạm vi rút gọn).
--}}
<footer class="site-footer on-dark">
    <div class="label-stripe" aria-hidden="true"></div>

    <div class="container site-footer__inner">
        <p class="footer-mark" aria-hidden="true">
            <span class="footer-mark__word">
                <span class="footer-mark__block footer-mark__block--1">m</span>
                <span class="footer-mark__block footer-mark__block--2">ô<span class="footer-mark__dot"></span></span>
                <span class="footer-mark__block footer-mark__block--3">c</span>
            </span>
            <span class="footer-mark__word">
                <span class="footer-mark__block footer-mark__block--4">l</span>
                <span class="footer-mark__block footer-mark__block--5">a</span>
                <span class="footer-mark__block footer-mark__block--6">m</span>
            </span>
        </p>

        <div class="site-footer__grid">
            <nav class="site-footer__col" aria-labelledby="footer-shop">
                <h2 id="footer-shop" class="site-footer__heading">Mua sắm</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ url('/danh-muc/lam-sach') }}" class="link-nav">Làm sạch</a></li>
                    <li><a href="{{ url('/danh-muc/duong-da') }}" class="link-nav">Dưỡng da</a></li>
                    <li><a href="{{ url('/danh-muc/mat-na') }}" class="link-nav">Mặt nạ</a></li>
                    <li><a href="{{ url('/danh-muc/chong-nang') }}" class="link-nav">Chống nắng</a></li>
                    <li><a href="{{ url('/san-pham?khuyen-mai=1') }}" class="link-nav">Đang ưu đãi</a></li>
                </ul>
            </nav>
            <nav class="site-footer__col" aria-labelledby="footer-help">
                <h2 id="footer-help" class="site-footer__heading">Hỗ trợ</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ route('faq.index') }}" class="link-nav">Câu hỏi thường gặp</a></li>
                    <li><a href="{{ route('contact.create') }}" class="link-nav">Liên hệ</a></li>
                    <li><a href="{{ route('account.orders.index') }}" class="link-nav">Đơn hàng của tôi</a></li>
                </ul>
            </nav>
            {{-- Chính sách đổi trả / vận chuyển nằm trong FAQ (trang tĩnh đã cắt khỏi phạm vi rút gọn) --}}
            <nav class="site-footer__col" aria-labelledby="footer-about">
                <h2 id="footer-about" class="site-footer__heading">Về Mộc Lam</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ route('home') }}#gioi-thieu" class="link-nav">Giới thiệu</a></li>
                    <li><a href="{{ route('account.dashboard') }}" class="link-nav">Tài khoản</a></li>
                </ul>
            </nav>
            <div class="site-footer__contact">
                <h2 class="site-footer__heading">Liên hệ</h2>
                <address>
                    <p>Hotline <a href="tel:18000000" class="link-nav tnum">1800 0000</a><br><span class="text-muted">8:00–21:00, cả cuối tuần</span></p>
                    <p><a href="mailto:lienhe@moclam.test" class="link-nav">lienhe@moclam.test</a></p>
                    <p>12 Lê Lợi, Phường Bến Thành, TP. Hồ Chí Minh</p>
                </address>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p class="mb-0">© {{ now()->year }} Mộc Lam. Mọi quyền được bảo lưu.</p>
            <a href="#top" class="link-nav">Lên đầu trang <x-icon name="arrow-right" :size="16" class="icon-up" /></a>
        </div>
    </div>
</footer>
