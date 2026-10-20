<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('title')
            @yield('title') · {{ config('app.name') }}
        @else
            {{ config('app.name') }} — Mỹ phẩm thảo mộc Việt
        @endif
    </title>
    <meta name="description" content="@yield('meta_description', 'Mộc Lam — chăm sóc da mặt từ thảo mộc Việt: làm sạch, dưỡng da, mặt nạ, chống nắng. Giao hàng toàn quốc, miễn phí từ 500.000 ₫.')">
    <meta name="theme-color" content="#F5F2EC">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <x-fonts />
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body id="top">
    <a href="#main" class="skip-link">Bỏ qua đến nội dung chính</a>

    <x-header />
    <x-flash />

    <main id="main" tabindex="-1">
        @yield('content')
    </main>

    <x-footer />
    <x-chat-widget />

    @stack('scripts')
</body>
</html>
