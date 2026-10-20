{{-- Bảng đơn hàng dùng chung cho tổng quan và danh sách --}}
<div class="table-responsive mb-4">
    <table class="table align-middle">
        <thead>
            <tr>
                <th scope="col">Mã đơn</th>
                <th scope="col">Ngày đặt</th>
                <th scope="col">Trạng thái</th>
                <th scope="col" class="text-end">Tổng tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td><a href="{{ route('account.orders.show', $order) }}" class="fw-medium">{{ $order->code }}</a></td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td><x-order-status :status="$order->status" /></td>
                    <td class="text-end tnum">{{ $order->formatted_total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
