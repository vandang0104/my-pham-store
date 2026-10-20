<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Đăng nhập quản trị · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <x-fonts />
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
    <main class="container py-9">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card card-body">
                    <h1 class="h4 mb-1">Đăng nhập quản trị</h1>
                    <p class="text-muted small mb-5">Dành cho quản trị viên và nhân viên {{ config('app.name') }}.</p>

                    <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check mb-5">
                            <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                            <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
                <p class="text-center mt-4"><a href="{{ route('home') }}">← Về cửa hàng</a></p>
            </div>
        </div>
    </main>
</body>
</html>
