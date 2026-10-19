{{-- Trang kiểm tra bằng mắt cho DESIGN.md — chỉ có ở môi trường local; bước 3.4 sẽ mở rộng --}}
@extends('layouts.app')

@section('title', 'Hệ thống thiết kế')

@push('styles')
    <meta name="robots" content="noindex">
@endpush

@section('content')
        <div class="on-dark">
            <div class="container py-9">
                <h1 class="t-display mb-5">Hệ thống thiết kế</h1>
                <p class="t-lead measure mb-0">Token màu, chữ, nút, form và các thành phần dùng chung theo DESIGN.md. Nhấn Tab để xem vòng focus.</p>
            </div>
        </div>

        <section class="sg-section" aria-labelledby="sg-thanh-phan">
            <div class="container">
                <x-section-heading id="sg-thanh-phan" title="Thẻ sản phẩm" lead="Đang giảm giá, giá thường, hết hàng; dải nhãn xoay vòng ba tông." link-url="#sg-thanh-phan" link-label="Xem tất cả" />
                @if ($cards->isNotEmpty())
                    <div class="product-grid">
                        @foreach ($cards as $i => $product)
                            <x-product-card :product="$product" :index="$i" />
                        @endforeach
                    </div>
                @else
                    <x-empty-state title="Chưa có sản phẩm" text="Chạy php artisan migrate:fresh --seed để có dữ liệu mẫu." :action-url="url('/')" action-label="Về trang chủ" />
                @endif

                <div class="mt-8">
                    <h3 class="mb-4">Giá và đánh giá</h3>
                    <div class="sg-demo-row">
                        @if ($cards->isNotEmpty())
                            <x-price :product="$cards->first()" size="lg" />
                        @endif
                        <x-rating :value="4.7" :count="23" />
                        <x-rating :value="3.4" :count="5" />
                        <x-rating :value="0" :count="0" />
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="mb-4">Breadcrumb (component)</h3>
                    <x-breadcrumb :items="[['label' => 'Chăm sóc da mặt', 'url' => url('/danh-muc/cham-soc-da-mat')], ['label' => 'Serum – Tinh chất', 'url' => url('/danh-muc/serum-tinh-chat')], ['label' => 'Serum Niacinamide 10% Sáng Da']]" />
                </div>

                <div class="mt-8">
                    <h3 class="mb-4">Phân trang (view mặc định)</h3>
                    {{ $paginated->links() }}
                </div>

                <div class="mt-8">
                    <h3 class="mb-4">Trạng thái trống</h3>
                    <x-empty-state title="Giỏ hàng của bạn đang trống" text="Xem các sản phẩm đang ưu đãi hoặc tìm theo loại da của bạn." :action-url="url('/san-pham')" action-label="Xem sản phẩm" />
                </div>

                <div class="mt-8">
                    <h3 class="mb-4">Icon</h3>
                    <div class="sg-demo-row">
                        @foreach ($icons as $icon)
                            <span class="d-inline-flex flex-column align-items-center gap-2 t-small"><x-icon :name="$icon" />{{ $icon }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-mau">
            <div class="container">
                <h2 id="sg-mau" class="mb-6">Bảng màu</h2>
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
                <h2 id="sg-chu" class="mb-4">Thang chữ</h2>
                <p class="measure">Năm bậc, không hơn. Cormorant Garamond chỉ dùng từ 28px; mọi chữ nhỏ hơn là Be Vietnam Pro.</p>
                @foreach ($typeSteps as $step)
                    <div class="sg-type-row">
                        <p class="t-small text-muted mb-0">{{ $step['label'] }}<br>{{ $step['spec'] }}</p>
                        <p class="{{ $step['class'] }} mb-0">{{ $step['sample'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-link">
            <div class="container">
                <h2 id="sg-link" class="mb-5">Link</h2>
                <p class="measure">Link trong đoạn văn luôn gạch chân khi nghỉ, ví dụ <a href="#sg-link">chính sách đổi trả trong 7 ngày</a> hoặc <a href="#sg-link">hướng dẫn chọn kem chống nắng</a>. Rê chuột để thấy gạch chân dày lên.</p>
                <nav aria-label="Ví dụ link điều hướng" class="sg-demo-row mt-5">
                    <a href="#sg-link" class="link-nav">Chăm sóc da</a>
                    <a href="#sg-link" class="link-nav" aria-current="page">Trang điểm</a>
                    <a href="#sg-link" class="link-nav">Nước hoa</a>
                    <a href="#sg-link" class="link-nav">Thương hiệu</a>
                </nav>
                <div class="on-dark p-6 mt-6">
                    <p class="mb-0 measure">Trên nền sơn mài: <a href="#sg-link">xem câu chuyện nguyên liệu</a> — vòng focus đổi sang champagne.</p>
                </div>
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-nut">
            <div class="container">
                <h2 id="sg-nut" class="mb-5">Nút</h2>
                <div class="sg-demo-row">
                    <button type="button" class="btn btn-primary">Thêm vào giỏ</button>
                    <button type="button" class="btn btn-text">Xem chi tiết</button>
                    <button type="button" class="btn btn-danger-text">Xoá khỏi giỏ</button>
                    <button type="button" class="btn btn-primary" disabled>Hết hàng</button>
                </div>
                <div class="on-dark p-6 mt-6">
                    <div class="sg-demo-row">
                        <button type="button" class="btn btn-champagne">Xem sản phẩm theo loại da</button>
                        <button type="button" class="btn btn-text">Tìm hiểu thêm</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-form">
            <div class="container">
                <h2 id="sg-form" class="mb-5">Form</h2>
                <form class="measure" novalidate>
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
                    <div class="form-field">
                        <button type="button" class="btn btn-primary w-100">Đặt hàng</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-flash">
            <div class="container">
                <h2 id="sg-flash" class="mb-5">Thông báo (flash)</h2>
            </div>
            <div class="flash flash--success" role="status">
                <div class="container flash__inner">
                    <p class="flash__text">Đã thêm Serum Niacinamide 10% vào giỏ hàng.</p>
                    <button type="button" class="flash__close" aria-label="Đóng thông báo">×</button>
                </div>
            </div>
            <div class="flash flash--error mt-2" role="alert">
                <div class="container flash__inner">
                    <p class="flash__text">Mã giảm giá đã hết lượt sử dụng.</p>
                    <button type="button" class="flash__close" aria-label="Đóng thông báo">×</button>
                </div>
            </div>
            <div class="flash flash--info mt-2" role="status">
                <div class="container flash__inner">
                    <p class="flash__text">Miễn phí giao hàng cho đơn từ 500.000&nbsp;₫.</p>
                    <button type="button" class="flash__close" aria-label="Đóng thông báo">×</button>
                </div>
            </div>
        </section>

        <section class="sg-section" aria-labelledby="sg-trang">
            <div class="container">
                <h2 id="sg-trang" class="mb-5">Phân trang và breadcrumb</h2>
                <nav aria-label="Phân trang">
                    <ul class="pagination">
                        <li class="page-item disabled"><span class="page-link">Trước</span></li>
                        <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
                        <li class="page-item"><a class="page-link" href="#sg-trang">2</a></li>
                        <li class="page-item"><a class="page-link" href="#sg-trang">3</a></li>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                        <li class="page-item"><a class="page-link" href="#sg-trang">8</a></li>
                        <li class="page-item"><a class="page-link" href="#sg-trang">Sau</a></li>
                    </ul>
                </nav>
                <nav class="pager mt-5 measure" aria-label="Phân trang rút gọn">
                    <a href="#sg-trang" class="link-nav">Trước</a>
                    <span class="pager__status">Trang 2/5</span>
                    <a href="#sg-trang" class="link-nav">Sau</a>
                </nav>
                <nav aria-label="Breadcrumb" class="mt-7">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#sg-trang">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="#sg-trang">Chăm sóc da mặt</a></li>
                        <li class="breadcrumb-item"><a href="#sg-trang">Serum – Tinh chất</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Serum Niacinamide 10% Sáng Da</li>
                    </ol>
                </nav>
            </div>
        </section>
@endsection
