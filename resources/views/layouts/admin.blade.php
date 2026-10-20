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
<body class="bg-body-tertiary">
    {{-- Khu quản trị (Bootstrap mặc định): topbar + sidebar theo quyền (docs/permissions.md) --}}
    <nav class="navbar bg-white border-bottom px-4 py-2" aria-label="Thanh quản trị">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#admin-sidebar"
                aria-controls="admin-sidebar" aria-expanded="false">Menu</button>
            <a href="{{ route('admin.dashboard') }}" class="navbar-brand fw-semibold mb-0">Quản trị {{ config('app.name') }}</a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small">{{ auth()->user()->name }} · <span class="text-muted">{{ auth()->user()->role->label() }}</span></span>
            <a href="{{ route('home') }}" class="small" target="_blank" rel="noopener">Xem cửa hàng</a>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Đăng xuất</button>
            </form>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 collapse d-lg-block bg-white border-end py-4" id="admin-sidebar" aria-label="Menu quản trị">
                <nav class="list-group list-group-flush">
                    @foreach ($adminMenu as $item)
                        <a href="{{ route($item['route']) }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs($item['active'])])
                            @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            </aside>

            <main class="col-lg-10 py-5 px-lg-6" id="main">
                <h1 class="h3 mb-5">@yield('title')</h1>
                <x-flash />
                @if ($errors->has('delete'))
                    <div class="alert alert-danger" role="alert">{{ $errors->first('delete') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
