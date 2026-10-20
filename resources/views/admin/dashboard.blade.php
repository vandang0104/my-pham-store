@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <p class="mb-5">Xin chào, <strong>{{ $user->name }}</strong> ({{ $user->role->label() }}).</p>

    <div class="row g-4 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-body h-100">
                <p class="text-muted small mb-1">Đơn chờ xác nhận</p>
                <p class="h3 mb-0" data-stat="pending">{{ $ops['pending'] }}</p>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-body h-100">
                <p class="text-muted small mb-1">Đơn hôm nay</p>
                <p class="h3 mb-0" data-stat="today">{{ $ops['today'] }}</p>
            </div>
        </div>
        @if ($revenue)
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100">
                    <p class="text-muted small mb-1">Doanh thu hôm nay (đơn đã giao)</p>
                    <p class="h3 mb-0" data-stat="revenue-today">{{ $revenue['today'] }}</p>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100">
                    <p class="text-muted small mb-1">Doanh thu tháng này</p>
                    <p class="h3 mb-0" data-stat="revenue-month">{{ $revenue['month'] }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-5">
        <div class="col-xl-7">
            <div class="card card-body">
                <h2 class="h5 mb-3">5 đơn mới nhất</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Mã đơn</th><th>Khách</th><th>Ngày</th><th>Trạng thái</th><th class="text-end">Tổng</th></tr></thead>
                        <tbody>
                            @forelse ($ops['latestOrders'] as $order)
                                <tr>
                                    <td>{{ $order->code }}</td>
                                    <td>{{ $order->receiver_name }}</td>
                                    <td>{{ $order->created_at->format('d/m H:i') }}</td>
                                    <td><x-order-status :status="$order->status" /></td>
                                    <td class="text-end">{{ $order->formatted_total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Chưa có đơn hàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card card-body">
                <h2 class="h5 mb-3">Sắp hết hàng (≤ {{ $lowStockThreshold }})</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">Tồn</th></tr></thead>
                        <tbody>
                            @forelse ($ops['lowStock'] as $product)
                                <tr>
                                    <td><a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a></td>
                                    <td>{{ $product->sku }}</td>
                                    <td class="text-end"><span @class(['badge', 'text-bg-danger' => $product->stock === 0, 'text-bg-warning' => $product->stock > 0])>{{ $product->stock }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Không có sản phẩm sắp hết.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
