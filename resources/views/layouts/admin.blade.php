<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · Quản trị {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <x-fonts />
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    {{-- Layout quản trị TẠM ở mức chức năng; layout admin thật (Bootstrap mặc định, menu bên) làm ở S7 --}}
    <main class="container py-9">
        <div class="row justify-content-center">
            {{-- Trang cần rộng hơn (vd chat) đặt @section('admin_col', 'col-lg-10') --}}
            <div class="col-12 @yield('admin_col', 'col-sm-10 col-md-7 col-lg-5')">
                <p class="text-center mb-6">
                    <a href="{{ route('home') }}" class="link-nav t-title"><x-wordmark /></a>
                </p>

                <div class="panel">
                    <h1 class="mb-5">@yield('title')</h1>

                    @if (session('status'))
                        <div class="flash flash--success flash--inline mb-5" role="status">
                            <p class="flash__text py-3">{{ session('status') }}</p>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </main>
</body>
</html>
