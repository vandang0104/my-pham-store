{{-- Khu tài khoản (Bootstrap mặc định): menu trái list-group, nội dung phải; điện thoại menu xếp trên. --}}
@extends('layouts.app')

@section('content')
    <div class="container py-6">
        <div class="row g-5">
            <aside class="col-lg-3" aria-label="Menu tài khoản">
                <nav class="list-group">
                    <a href="{{ route('account.dashboard') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('account.dashboard')])
                        @if (request()->routeIs('account.dashboard')) aria-current="page" @endif>Tổng quan</a>
                    <a href="{{ route('account.profile.edit') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('account.profile.*')])
                        @if (request()->routeIs('account.profile.*')) aria-current="page" @endif>Hồ sơ</a>
                    <a href="{{ route('account.orders.index') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('account.orders.*')])
                        @if (request()->routeIs('account.orders.*')) aria-current="page" @endif>Đơn hàng</a>
                    <a href="{{ route('account.password.edit') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('account.password.*')])
                        @if (request()->routeIs('account.password.*')) aria-current="page" @endif>Đổi mật khẩu</a>
                    <form method="POST" action="{{ route('logout') }}" class="list-group-item p-0">
                        @csrf
                        <button type="submit" class="btn btn-link w-100 text-start px-4 py-3">Đăng xuất</button>
                    </form>
                </nav>
            </aside>

            <div class="col-lg-9">
                <h1 class="t-title mb-5">@yield('title')</h1>
                @yield('account')
            </div>
        </div>
    </div>
@endsection
