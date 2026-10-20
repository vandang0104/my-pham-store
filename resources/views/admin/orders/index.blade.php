@extends('layouts.admin')

@section('title', 'Đơn hàng')

@section('content')
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="q" class="form-label">Tìm mã đơn / tên / SĐT</label>
            <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <label for="trang-thai" class="form-label">Trạng thái</label>
            <select id="trang-thai" name="trang-thai" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['trang-thai'] === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-outline-secondary">Lọc</button></div>
    </form>

    <p class="text-muted small">{{ $orders->total() }} đơn</p>
    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Mã đơn</th><th>Người nhận</th><th>SĐT</th><th>Ngày đặt</th><th>Trạng thái</th><th>Thanh toán</th><th class="text-end">Tổng</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-medium">{{ $order->code }}</a></td>
                            <td>{{ $order->receiver_name }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td><x-order-status :status="$order->status" /></td>
                            <td class="small">{{ $order->payment_method->label() }}<br><span class="text-muted">{{ $order->payment_status->label() }}</span></td>
                            <td class="text-end">{{ $order->formatted_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Không có đơn phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $orders->links() }}
@endsection
