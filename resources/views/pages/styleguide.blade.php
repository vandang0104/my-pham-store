{{-- Trang kiểm tra bằng mắt cho DESIGN.md — chỉ có ở môi trường local. Mọi mẫu dùng component thật, không HTML chép tay. --}}
@extends('layouts.app')

@section('title', 'Hệ thống thiết kế')

@push('styles')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <div class="on-dark">
        <div class="container py-9 sg-intro">
            <div>
                <h1 class="t-display mb-5">Hệ thống thiết kế</h1>
                <p class="t-lead measure mb-0">Token màu, chữ, nút, form và các thành phần dùng chung theo DESIGN.md. Nhấn Tab để xem vòng focus.</p>
            </div>
            <nav aria-label="Mục của trang" class="sg-toc">
                <ul class="list-unstyled mb-0">
                    @foreach ($sections as $id => $label)
                        <li><a href="#{{ $id }}" class="link-nav">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>

    <section class="sg-section" aria-labelledby="sg-mau">
        <div class="container">
            <h2 id="sg-mau" class="mb-6">{{ $sections['sg-mau'] }}</h2>
            <div class="sg-swatches">
                @foreach ($colors as $color)
                    <div class="sg-swatch">
                        <div class="sg-swatch__chip" style="background: var({{ $color['token'] }});" aria-hidden="true"></div>
                        <p class="sg-swatch__meta mb-0">
                            <strong>{{ $color['name'] }}</strong> · <span class="tnum">{{ $color['hex'] }}</span><br>
                            <span class="text-muted">{{ $color['token'] }} — {{ $color['role'] }}</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-chu">
        <div class="container">
            <h2 id="sg-chu" class="mb-4">{{ $sections['sg-chu'] }}</h2>
            <p class="measure">Năm bậc, không hơn. Cormorant Garamond chỉ dùng từ 28px; mọi chữ nhỏ hơn là Be Vietnam Pro.</p>
            @foreach ($typeSteps as $step)
                <div class="sg-type-row">
                    <p class="sg-type-row__spec mb-0"><strong>{{ $step['label'] }}</strong> — {{ $step['spec'] }}</p>
                    <p class="{{ $step['class'] }} mb-0">{{ $step['sample'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-link">
        <div class="container">
            <h2 id="sg-link" class="mb-5">{{ $sections['sg-link'] }}</h2>
            <div class="sg-pair">
                <div>
                    <p class="measure">Link trong đoạn văn luôn gạch chân khi nghỉ, ví dụ <a href="#sg-link">chính sách đổi trả trong 7 ngày</a> hoặc <a href="#sg-link">hướng dẫn chọn kem chống nắng</a>. Rê chuột để thấy gạch chân dày lên.</p>
                    <nav aria-label="Ví dụ link điều hướng" class="sg-demo-row mt-5">
                        <a href="#sg-link" class="link-nav">Làm sạch</a>
                        <a href="#sg-link" class="link-nav" aria-current="page">Dưỡng da</a>
                        <a href="#sg-link" class="link-nav">Mặt nạ</a>
                        <a href="#sg-link" class="link-nav">Thương hiệu</a>
                    </nav>
                </div>
                <div class="on-dark p-6">
                    <p class="mb-0 measure">Trên nền sơn mài: <a href="#sg-link">xem câu chuyện nguyên liệu</a> — vòng focus đổi sang champagne.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-nut">
        <div class="container">
            <h2 id="sg-nut" class="mb-5">{{ $sections['sg-nut'] }}</h2>
            <div class="sg-pair">
                <div class="sg-demo-row">
                    <button type="button" class="btn btn-primary">Thêm vào giỏ</button>
                    <button type="button" class="btn btn-text">Xem chi tiết</button>
                    <button type="button" class="btn btn-danger-text">Xoá khỏi giỏ</button>
                    <button type="button" class="btn btn-primary" disabled>Hết hàng</button>
                    <button type="button" class="btn btn-text" disabled>Không khả dụng</button>
                </div>
                <div class="on-dark p-6">
                    <div class="sg-demo-row">
                        <button type="button" class="btn btn-champagne">Xem sản phẩm theo loại da</button>
                        <button type="button" class="btn btn-text">Tìm hiểu thêm</button>
                        <button type="button" class="btn btn-champagne" disabled>Đã hết suất</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-form">
        <div class="container">
            <h2 id="sg-form" class="mb-5">{{ $sections['sg-form'] }}</h2>
            <form class="sg-form" novalidate>
                <div class="form-field">
                    <label for="sg-name" class="form-label">Họ tên <span class="form-required">(bắt buộc)</span></label>
                    <input id="sg-name" type="text" class="form-control" value="Nguyễn Thị Lan" autocomplete="name">
                </div>
                <div class="form-field">
                    <label for="sg-email" class="form-label">Email <span class="form-required">(bắt buộc)</span></label>
                    <input id="sg-email" type="email" class="form-control is-invalid" value="lan@" aria-describedby="sg-email-error" aria-invalid="true">
                    <div id="sg-email-error" class="invalid-feedback">Email phải là địa chỉ email hợp lệ.</div>
                </div>
                <div class="form-field">
                    <label for="sg-skin" class="form-label">Loại da</label>
                    <select id="sg-skin" class="form-select" aria-describedby="sg-skin-help">
                        @foreach ($skinTypes as $skinType)
                            <option value="{{ $skinType->value }}">{{ $skinType->label() }}</option>
                        @endforeach
                    </select>
                    <div id="sg-skin-help" class="form-text">Dùng để gợi ý sản phẩm phù hợp.</div>
                </div>
                <div class="form-field">
                    <label for="sg-phone" class="form-label">Số điện thoại</label>
                    <input id="sg-phone" type="tel" class="form-control" value="0901 234 567" disabled>
                    <div class="form-text">Ô bị khoá: liên hệ cửa hàng để đổi số.</div>
                </div>
                <div class="form-field sg-form__wide">
                    <label for="sg-note" class="form-label">Ghi chú cho đơn hàng</label>
                    <textarea id="sg-note" class="form-control" rows="3">Giao giờ hành chính giúp mình.</textarea>
                </div>
                <fieldset class="form-field">
                    <legend class="form-label">Phương thức thanh toán</legend>
                    <div class="form-check">
                        <input id="sg-cod" class="form-check-input" type="radio" name="sg-pay" checked>
                        <label for="sg-cod" class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="form-check">
                        <input id="sg-bank" class="form-check-input" type="radio" name="sg-pay">
                        <label for="sg-bank" class="form-check-label">Chuyển khoản ngân hàng</label>
                    </div>
                </fieldset>
                <div class="form-field">
                    <div class="form-check">
                        <input id="sg-remember" class="form-check-input" type="checkbox" checked>
                        <label for="sg-remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                    </div>
                </div>
                <div class="form-field sg-form__wide">
                    <button type="button" class="btn btn-primary">Đặt hàng</button>
                </div>
            </form>
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-the">
        <div class="container">
            <x-section-heading id="sg-the" :title="$sections['sg-the']" lead="Đang giảm giá, giá thường, hết hàng; dải nhãn xoay vòng ba tông." link-url="#sg-the" link-label="Xem tất cả" />
            @if ($cards->isNotEmpty())
                <div class="product-grid">
                    @foreach ($cards as $i => $product)
                        <x-product-card :product="$product" :index="$i" />
                    @endforeach
                </div>
            @else
                <x-empty-state title="Chưa có sản phẩm" text="Chạy php artisan migrate:fresh --seed để có dữ liệu mẫu." :action-url="url('/')" action-label="Về trang chủ" />
            @endif

            @if ($cards->isNotEmpty())
                <h3 class="mt-8 mb-4">Giá</h3>
                <x-price :product="$cards->first()" size="lg" />
            @endif
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-badge">
        <div class="container">
            <h2 id="sg-badge" class="mb-4">{{ $sections['sg-badge'] }}</h2>
            <p class="measure mb-6">Chỉ hai loại: số đếm trên túi hàng (ẩn khi 0, trên 99 hiện "99+") và phần trăm giảm.</p>
            <div class="sg-demo-row sg-demo-row--wide">
                @foreach ($badgeCounts as $count)
                    <figure class="sg-specimen">
                        <span class="icon-btn icon-btn--cart" role="img" aria-label="Túi hàng có {{ $count }} sản phẩm">
                            <x-icon name="bag" />
                            <x-cart-badge :count="$count" />
                        </span>
                        <figcaption class="sg-specimen__caption">{{ $count === 0 ? '0 — ẩn' : $count }}</figcaption>
                    </figure>
                @endforeach
                <figure class="sg-specimen">
                    <x-discount-badge :percent="20" :decorative="false" />
                    <figcaption class="sg-specimen__caption">Giảm giá</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="sg-section sg-section--bleed" aria-labelledby="sg-flash">
        <div class="container">
            <h2 id="sg-flash" class="mb-4">{{ $sections['sg-flash'] }}</h2>
            <p class="measure mb-6">"Alert" của dự án chính là flash (không dùng .alert của Bootstrap). Lỗi không tự ẩn; mẫu ở đây tắt tự ẩn để xem.</p>
        </div>
        <div class="sg-flash-stack">
            <x-flash-message type="success" message="Đã thêm Serum Niacinamide 10% vào giỏ hàng." :autohide="false" />
            <x-flash-message type="error" message="Mã giảm giá đã hết lượt sử dụng." />
            <x-flash-message type="info" message="Miễn phí giao hàng cho đơn từ 500.000 ₫." :autohide="false" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-breadcrumb">
        <div class="container">
            <h2 id="sg-breadcrumb" class="mb-5">{{ $sections['sg-breadcrumb'] }}</h2>
            <x-breadcrumb :items="[['label' => 'Dưỡng da', 'url' => url('/danh-muc/duong-da')], ['label' => 'Serum – Tinh chất', 'url' => url('/danh-muc/serum-tinh-chat')], ['label' => 'Serum Niacinamide 10% Sáng Da']]" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-phan-trang">
        <div class="container">
            <h2 id="sg-phan-trang" class="mb-5">{{ $sections['sg-phan-trang'] }}</h2>
            {{ $paginated->links() }}
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-skeleton">
        <div class="container">
            <h2 id="sg-skeleton" class="mb-4">{{ $sections['sg-skeleton'] }}</h2>
            <p class="measure mb-6">Mảng phẳng giữ khung của thẻ và dòng danh sách; chỉ nhịp mờ chậm, đứng yên khi giảm chuyển động.</p>
            <x-skeleton.product-grid :count="4" />
            <x-skeleton.list :count="3" class="sg-skeleton-list mt-8" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-trong">
        <div class="container">
            <h2 id="sg-trong" class="mb-5">{{ $sections['sg-trong'] }}</h2>
            <x-empty-state title="Giỏ hàng của bạn đang trống" text="Xem các sản phẩm đang ưu đãi hoặc tìm theo loại da của bạn." :action-url="url('/san-pham')" action-label="Xem sản phẩm" />
        </div>
    </section>

    <section class="sg-section" aria-labelledby="sg-icon">
        <div class="container">
            <h2 id="sg-icon" class="mb-5">{{ $sections['sg-icon'] }}</h2>
            <div class="sg-icons">
                @foreach ($icons as $icon)
                    <figure class="sg-specimen">
                        <x-icon :name="$icon" />
                        <figcaption class="sg-specimen__caption">{{ $icon }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endsection
