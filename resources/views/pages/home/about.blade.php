{{--
    Giới thiệu cửa hàng (bắt buộc theo đề cương). Nội dung MẪU — cần chủ cửa hàng duyệt; không bịa danh tính, số liệu.
    Ngưỡng miễn phí giao hàng đọc từ settings (free_shipping_from).
--}}
<section id="gioi-thieu" class="home-about section" aria-labelledby="about-title">
    <div class="container">
        <div class="home-about__body">
            <h2 id="about-title" class="t-title mb-5">Về Mộc Lam</h2>
            <p>Mộc Lam là cửa hàng mỹ phẩm thảo mộc trực tuyến. Chúng tôi chỉ bán sản phẩm chăm sóc da mặt — làm sạch, dưỡng da, mặt nạ và chống nắng — từ những thương hiệu làm việc với nguyên liệu trồng ở Việt Nam.</p>
            <p>Mỗi sản phẩm ghi rõ thành phần chính, nơi trồng và loại da phù hợp. Chúng tôi nói đúng công dụng, không hứa điều sản phẩm không làm được.</p>
            <p>Bạn có thể đặt hàng mà không cần tài khoản, thanh toán khi nhận hàng hoặc chuyển khoản. Đơn từ {{ $freeShippingFrom }} được miễn phí giao hàng.</p>
            <a href="{{ url('/san-pham') }}" class="btn btn-primary mt-4">Xem tất cả sản phẩm</a>
        </div>
    </div>
</section>
