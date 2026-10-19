@extends('layouts.auth')

@section('title', 'Quên mật khẩu')

@section('form')
    <p class="text-secondary">Nhập email đã đăng ký, chúng tôi sẽ gửi liên kết để bạn đặt lại mật khẩu.</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="email">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Gửi liên kết đặt lại mật khẩu</button>
    </form>

    <p class="text-center mt-4 mb-0">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </p>
@endsection
