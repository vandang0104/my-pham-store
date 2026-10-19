@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
            </div>
            <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn btn-dark w-100">Đăng nhập</button>
    </form>

    <p class="text-center mt-4 mb-0">
        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a>
    </p>
@endsection
