{{--
    6. Lời mời đảo màu (CTA): mảng sơn mài full-bleed, câu ý tưởng ở cỡ lớn nhất 2 dòng, chân mảng một câu + nút champagne.
    "Đơn chăm sóc" (tuỳ chọn) chưa làm → nút tới /san-pham lọc theo loại da (loại da trong hồ sơ nếu khách đã lưu).
--}}
<section class="home-invite on-dark" aria-labelledby="invite-title">
    <div class="container home-invite__inner">
        {{-- Câu ý tưởng (DESIGN.md §2) đúng 2 dòng ở mọi bề rộng: mỗi dòng ≤ 18 ký tự --}}
        <h2 id="invite-title" class="t-display home-invite__title" data-reveal>
            <span class="d-block">Mỗi nhãn ghi rõ</span>
            <span class="d-block">nơi cây được trồng</span>
        </h2>
        <div class="home-invite__foot" data-reveal>
            <p class="t-lead home-invite__text">Chọn đúng cho làn da của bạn.</p>
            <a href="{{ $skinTypeUrl }}" class="btn btn-champagne">Xem sản phẩm theo loại da</a>
        </div>
    </div>
</section>
