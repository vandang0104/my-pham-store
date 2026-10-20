@extends('layouts.admin')

@section('title', 'Đơn '.$order->code)

@section('content')
    <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
        <x-order-status :status="$order->status" />
        <span class="badge text-bg-light">{{ $order->payment_method->label() }} · {{ $order->payment_status->label() }}</span>
        <span class="text-muted small">Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }}</span>
        <a href="{{ route('admin.orders.index') }}" class="ms-auto small">← Danh sách đơn</a>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            <div class="card card-body mb-5">
                <h2 class="h5 mb-3">Sản phẩm</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="small">{{ $item->sku }}</td>
                                    <td class="text-end">{{ $item->formatted_price }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $item->formatted_line_total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <dl class="row mt-3 mb-0">
                    <dt class="col-8 text-end fw-normal">Tạm tính</dt><dd class="col-4 text-end">{{ $money['subtotal'] }}</dd>
                    <dt class="col-8 text-end fw-normal">Phí giao hàng</dt><dd class="col-4 text-end">{{ $money['shipping'] }}</dd>
                    <dt class="col-8 text-end">Tổng cộng</dt><dd class="col-4 text-end fw-bold">{{ $money['total'] }}</dd>
                </dl>
            </div>

            <div class="card card-body">
                <h2 class="h5 mb-3">Lịch sử trạng thái</h2>
                <ul class="list-group list-group-flush">
                    @foreach ($order->statusHistories as $history)
                        <li class="list-group-item px-0 d-flex flex-wrap justify-content-between gap-2">
                            <span>
                                @if ($history->from_status) {{ $history->from_status->label() }} → @endif
                                <x-order-status :status="$history->to_status" />
                                @if ($history->note) <span class="ms-2">{{ $history->note }}</span> @endif
                            </span>
                            <span class="small text-muted">{{ $history->changedBy?->name ?? 'Khách / hệ thống' }} · {{ $history->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-body mb-5">
                <h2 class="h5 mb-3">Khách hàng</h2>
                <p class="mb-1"><strong>{{ $order->receiver_name }}</strong> · {{ $order->phone }}</p>
                <p class="mb-1">{{ $order->email }}</p>
                <p class="mb-1">{{ $order->full_address }}</p>
                <p class="mb-0 small text-muted">{{ $order->user ? 'Tài khoản: '.$order->user->email : 'Khách vãng lai' }}</p>
                @if ($order->note) <p class="mt-2 mb-0">Ghi chú: {{ $order->note }}</p> @endif
            </div>

            @can('updateStatus', $order)
                @if ($next !== [])
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="card card-body mb-5">
                        @csrf
                        @method('PATCH')
                        <h2 class="h5 mb-3">Đổi trạng thái</h2>
                        <label for="status" class="form-label">Chuyển sang</label>
                        <select id="status" name="status" class="form-select mb-3">
                            @foreach ($next as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <label for="note" class="form-label">Ghi chú (không bắt buộc)</label>
                        <input id="note" name="note" type="text" maxlength="255" class="form-control mb-3">
                        <button type="submit" class="btn btn-primary" onclick="return this.form.status.value !== 'cancelled' || confirm('Huỷ đơn và hoàn kho?');">Cập nhật</button>
                    </form>
                @endif
            @endcan

            @if ($order->payment_status->value === 'unpaid' && $order->status->value !== 'cancelled')
                @can('updatePayment', $order)
                    <form method="POST" action="{{ route('admin.orders.paid', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-success w-100">Đánh dấu đã thanh toán</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>
@endsection
