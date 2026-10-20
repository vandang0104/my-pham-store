@extends('account.layout')

@section('title', 'Đơn hàng '.$order->code)

@section('account')
    <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
        <x-order-status :status="$order->status" />
        <span class="text-muted">Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }}</span>
        @if ($canCancel)
            <form method="POST" action="{{ route('account.orders.cancel', $order) }}" class="ms-auto"
                onsubmit="return confirm('Huỷ đơn {{ $order->code }}? Thao tác này không hoàn tác được.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Huỷ đơn</button>
            </form>
        @endif
    </div>

    <div class="card card-body mb-5">
        <h2 class="h5 mb-4">Sản phẩm</h2>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col" class="text-end">Đơn giá</th>
                        <th scope="col" class="text-end">SL</th>
                        <th scope="col" class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }} <span class="small text-muted">({{ $item->sku }})</span></td>
                            <td class="text-end tnum">{{ $item->formatted_price }}</td>
                            <td class="text-end tnum">{{ $item->quantity }}</td>
                            <td class="text-end tnum">{{ $item->formatted_line_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <dl class="row mt-4 mb-0">
            <dt class="col-8 fw-normal text-end">Tạm tính</dt>
            <dd class="col-4 text-end tnum">{{ $money['subtotal'] }}</dd>
            <dt class="col-8 fw-normal text-end">Phí giao hàng</dt>
            <dd class="col-4 text-end tnum">{{ $money['shipping'] }}</dd>
            <dt class="col-8 text-end">Tổng cộng</dt>
            <dd class="col-4 text-end tnum fw-bold">{{ $money['total'] }}</dd>
        </dl>
    </div>

    <div class="row g-5 mb-5">
        <div class="col-md-6">
            <div class="card card-body h-100">
                <h2 class="h5 mb-3">Giao tới</h2>
                <p class="mb-1"><strong>{{ $order->receiver_name }}</strong> · {{ $order->phone }}</p>
                <p class="mb-0">{{ $order->full_address }}</p>
                @if ($order->note)
                    <p class="mt-2 mb-0 text-muted">Ghi chú: {{ $order->note }}</p>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-body h-100">
                <h2 class="h5 mb-3">Thanh toán</h2>
                <p class="mb-1">{{ $order->payment_method->label() }}</p>
                <p class="mb-0">Tình trạng: {{ $order->payment_status->label() }}</p>
            </div>
        </div>
    </div>

    <div class="card card-body">
        <h2 class="h5 mb-3">Lịch sử trạng thái</h2>
        <ul class="list-group list-group-flush">
            @foreach ($order->statusHistories as $history)
                <li class="list-group-item px-0 d-flex flex-wrap justify-content-between gap-2">
                    <span><x-order-status :status="$history->to_status" />
                        @if ($history->note) <span class="ms-2">{{ $history->note }}</span> @endif</span>
                    <span class="small text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
