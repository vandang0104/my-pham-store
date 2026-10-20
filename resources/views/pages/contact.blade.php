{{-- Liên hệ (Bootstrap mặc định): form bên trái, thông tin cửa hàng bên phải. Ô "website" là honeypot chống spam. --}}
@extends('layouts.app')

@section('title', 'Liên hệ')

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="[['label' => 'Liên hệ']]" class="mb-4" />
        <h1 class="t-title mb-5">Liên hệ</h1>

        <div class="row g-6">
            <div class="col-lg-7">
                @error('throttle')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror

                <form method="POST" action="{{ route('contact.store') }}" class="card card-body" novalidate>
                    @csrf
                    {{-- Honeypot: người thật không thấy ô này; bot tự điền → tin bị bỏ --}}
                    <div class="visually-hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" type="text" name="{{ $honeypot }}" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Họ tên <span class="form-required">(bắt buộc)</span></label>
                            <input id="name" name="name" type="text" maxlength="100" autocomplete="name" required
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()?->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="form-required">(bắt buộc)</span></label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()?->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel"
                                class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()?->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="subject" class="form-label">Chủ đề <span class="form-required">(bắt buộc)</span></label>
                            <input id="subject" name="subject" type="text" maxlength="150" required
                                class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}">
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Nội dung <span class="form-required">(bắt buộc)</span></label>
                            <textarea id="message" name="message" rows="5" minlength="10" maxlength="2000" required aria-describedby="message-help"
                                class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                            <div id="message-help" class="form-text">Từ 10 đến 2.000 ký tự.</div>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
                    </div>
                </form>
            </div>

            <aside class="col-lg-5" aria-labelledby="store-info">
                <div class="card card-body">
                    <h2 id="store-info" class="h5 mb-4">Thông tin cửa hàng</h2>
                    <dl class="mb-0">
                        <dt>Hotline</dt>
                        <dd><a href="tel:{{ $store['hotline_tel'] }}">{{ $store['hotline'] }}</a></dd>
                        <dt>Email</dt>
                        <dd><a href="mailto:{{ $store['email'] }}">{{ $store['email'] }}</a></dd>
                        <dt>Địa chỉ</dt>
                        <dd>{{ $store['address'] }}</dd>
                        <dt>Giờ làm việc</dt>
                        <dd class="mb-0">{{ $store['hours'] }}</dd>
                    </dl>
                </div>
                <p class="text-muted small mt-3">Câu hỏi về đơn hàng, đổi trả, vận chuyển? Xem <a href="{{ route('faq.index') }}">câu hỏi thường gặp</a>.</p>
            </aside>
        </div>
    </div>
@endsection
