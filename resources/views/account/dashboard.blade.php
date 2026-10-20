@extends('account.layout')

@section('title', 'Tổng quan')

@section('account')
    <p class="mb-5">Xin chào, <strong>{{ $user->name }}</strong>. Tại đây bạn xem đơn hàng, sửa hồ sơ và đổi mật khẩu.</p>

    <h2 class="h5 mb-3">Đơn hàng gần đây</h2>
    @if ($recentOrders->isEmpty())
        <x-empty-state title="Bạn chưa có đơn hàng nào" text="Khám phá sản phẩm và đặt đơn đầu tiên." :action-url="route('products.index')" action-label="Xem sản phẩm" />
    @else
        @include('account.orders._table', ['orders' => $recentOrders])
        <a href="{{ route('account.orders.index') }}">Xem tất cả đơn hàng</a>
    @endif
@endsection
