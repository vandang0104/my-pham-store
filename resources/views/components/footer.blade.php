{{--
    Footer — "mặt sau của tấm nhãn" (DESIGN.md §9): nền sơn mài, dải sọc nhãn ở mép trên,
    chữ thương hiệu dựng khối toàn chiều rộng, 4 cột link, liên hệ, bản quyền.
    Thông tin liên hệ là dữ liệu MẪU — sẽ đọc từ bảng settings ở bước 9.6.
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
                    <li><a href="{{ url('/danh-muc/cham-soc-da-mat') }}" class="link-nav">Chăm sóc da mặt</a></li>
                    <li><a href="{{ url('/danh-muc/trang-diem') }}" class="link-nav">Trang điểm</a></li>
                    <li><a href="{{ url('/danh-muc/nuoc-hoa') }}" class="link-nav">Nước hoa</a></li>
                    <li><a href="{{ url('/san-pham?khuyen-mai=1') }}" class="link-nav">Đang ưu đãi</a></li>
                </ul>
            </nav>
            <nav class="site-footer__col" aria-labelledby="footer-help">
                <h2 id="footer-help" class="site-footer__heading">Hỗ trợ</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ url('/tra-cuu-don-hang') }}" class="link-nav">Tra cứu đơn hàng</a></li>
                    <li><a href="{{ url('/cau-hoi-thuong-gap') }}" class="link-nav">Câu hỏi thường gặp</a></li>
                    <li><a href="{{ url('/huong-dan-mua-hang') }}" class="link-nav">Hướng dẫn mua hàng</a></li>
                    <li><a href="{{ url('/lien-he') }}" class="link-nav">Liên hệ</a></li>
                </ul>
            </nav>
            <nav class="site-footer__col" aria-labelledby="footer-policy">
                <h2 id="footer-policy" class="site-footer__heading">Chính sách</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ url('/chinh-sach-doi-tra') }}" class="link-nav">Đổi trả trong 7 ngày</a></li>
                    <li><a href="{{ url('/chinh-sach-van-chuyen') }}" class="link-nav">Vận chuyển</a></li>
                    <li><a href="{{ url('/chinh-sach-bao-mat') }}" class="link-nav">Bảo mật thông tin</a></li>
                </ul>
            </nav>
            <nav class="site-footer__col" aria-labelledby="footer-about">
                <h2 id="footer-about" class="site-footer__heading">Về Mộc Lam</h2>
                <ul class="site-footer__links">
                    <li><a href="{{ url('/gioi-thieu') }}" class="link-nav">Câu chuyện</a></li>
                    <li><a href="{{ url('/tin-tuc') }}" class="link-nav">Tin tức</a></li>
                    <li><a href="{{ url('/tai-khoan') }}" class="link-nav">Tài khoản</a></li>
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
