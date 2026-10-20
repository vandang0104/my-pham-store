{{-- Thanh toán (Bootstrap mặc định): trái form người nhận + địa chỉ + phương thức; phải tóm tắt đơn. Một nút "Đặt hàng". --}}
@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="[['label' => 'Giỏ hàng', 'url' => route('cart.index')], ['label' => 'Thanh toán']]" class="mb-4" />
        <h1 class="t-title mb-5">Thanh toán</h1>

        <form method="POST" action="{{ route('checkout.store') }}" class="row g-6" novalidate>
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $token }}">

            <div class="col-lg-7">
                <fieldset class="card card-body mb-5">
                    <legend class="h5 mb-4">Thông tin người nhận</legend>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="receiver_name" class="form-label">Họ tên <span class="form-required">(bắt buộc)</span></label>
                            <input id="receiver_name" name="receiver_name" type="text" maxlength="100" autocomplete="name" required
                                class="form-control @error('receiver_name') is-invalid @enderror" value="{{ old('receiver_name', $defaults['receiver_name']) }}">
                            @error('receiver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Số điện thoại <span class="form-required">(bắt buộc)</span></label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="09xxxxxxxx" required
                                class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $defaults['phone']) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email @guest <span class="form-required">(bắt buộc)</span> @endguest</label>
                            <input id="email" name="email" type="email" autocomplete="email" @guest required @endguest aria-describedby="email-help"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $defaults['email']) }}">
                            <div id="email-help" class="form-text">Chúng tôi gửi email xác nhận đơn tới địa chỉ này.</div>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="card card-body mb-5">
                    <legend class="h5 mb-4">Địa chỉ giao hàng</legend>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="province" class="form-label">Tỉnh / thành phố <span class="form-required">(bắt buộc)</span></label>
                            <input id="province" name="province" type="text" maxlength="100" required autocomplete="address-level1"
                                class="form-control @error('province') is-invalid @enderror" value="{{ old('province') }}">
                            @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="district" class="form-label">Quận / huyện</label>
                            <input id="district" name="district" type="text" maxlength="100" autocomplete="address-level2" aria-describedby="district-help"
                                class="form-control @error('district') is-invalid @enderror" value="{{ old('district') }}">
                            <div id="district-help" class="form-text">Bỏ trống nếu địa chỉ chỉ còn 2 cấp.</div>
                            @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="ward" class="form-label">Phường / xã <span class="form-required">(bắt buộc)</span></label>
                            <input id="ward" name="ward" type="text" maxlength="100" required autocomplete="address-level3"
                                class="form-control @error('ward') is-invalid @enderror" value="{{ old('ward') }}">
                            @error('ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="detail" class="form-label">Địa chỉ chi tiết <span class="form-required">(bắt buộc)</span></label>
                            <input id="detail" name="detail" type="text" maxlength="255" required autocomplete="street-address" placeholder="Số nhà, tên đường"
                                class="form-control @error('detail') is-invalid @enderror" value="{{ old('detail') }}">
                            @error('detail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea id="note" name="note" rows="2" maxlength="500" class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="card card-body mb-5">
                    <legend class="h5 mb-4">Phương thức thanh toán</legend>
                    @foreach ($paymentMethods as $method)
                        <div class="form-check mb-2">
                            <input id="pm-{{ $method->value }}" class="form-check-input @error('payment_method') is-invalid @enderror" type="radio" name="payment_method"
                                value="{{ $method->value }}" @checked(old('payment_method', 'cod') === $method->value)>
                            <label for="pm-{{ $method->value }}" class="form-check-label">{{ $method->label() }}</label>
                        </div>
                    @endforeach
                    <div class="form-text">Chọn chuyển khoản: thông tin tài khoản hiện ở trang xác nhận và trong email; đơn được xác nhận sau khi nhận tiền.</div>
                    @error('payment_method') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </fieldset>
            </div>

            <div class="col-lg-5">
                <div class="card card-body">
                    <h2 class="h5 mb-4">Đơn hàng của bạn</h2>
                    <ul class="list-group list-group-flush mb-4">
                        @foreach ($items as $item)
                            <li class="list-group-item d-flex justify-content-between gap-3 px-0">
                                <span>{{ $item->product->name }} <span class="text-muted">× {{ $item->quantity }}</span></span>
                                <span class="tnum text-nowrap">{{ $item->formattedTotal }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="row mb-0">
                        <dt class="col-6 fw-normal">Tạm tính</dt>
                        <dd class="col-6 text-end tnum">{{ $summary['subtotal'] }}</dd>
                        <dt class="col-6 fw-normal">Phí giao hàng</dt>
                        <dd class="col-6 text-end tnum">{{ $summary['shipping'] }}</dd>
                        <dt class="col-6">Tổng cộng</dt>
                        <dd class="col-6 text-end tnum fw-bold" data-checkout-total>{{ $summary['total'] }}</dd>
                    </dl>
                    @if ($summary['remaining'])
                        <p class="small text-muted mt-2 mb-0">Mua thêm {{ $summary['remaining'] }} để được miễn phí giao hàng.</p>
                    @endif
                    <button type="submit" class="btn btn-primary w-100 mt-5">Đặt hàng</button>
                    <a href="{{ route('cart.index') }}" class="text-center mt-3">Quay lại giỏ hàng</a>
                </div>
            </div>
        </form>
    </div>
@endsection
