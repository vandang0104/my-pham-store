<x-mail::message>
# Cảm ơn bạn đã đặt hàng!

Xin chào **{{ $order->receiver_name }}**, Mộc Lam đã nhận đơn hàng **{{ $order->code }}** của bạn.

<x-mail::table>
| Sản phẩm | SL | Thành tiền |
|:--|:-:|--:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ $money($item->line_total) }} |
@endforeach
| Tạm tính | | {{ $money($order->subtotal) }} |
| Phí giao hàng | | {{ $order->shipping_fee > 0 ? $money($order->shipping_fee) : 'Miễn phí' }} |
| **Tổng cộng** | | **{{ $money($order->total) }}** |
</x-mail::table>

**Phương thức thanh toán:** {{ $order->payment_method->label() }}

**Giao tới:** {{ $order->full_address }} — {{ $order->phone }}

@if ($bank)
<x-mail::panel>
**Thông tin chuyển khoản**<br>
Ngân hàng: {{ $bank['name'] }}<br>
Số tài khoản: {{ $bank['number'] }}<br>
Chủ tài khoản: {{ $bank['holder'] }}<br>
Số tiền: **{{ $money($order->total) }}**<br>
Nội dung chuyển khoản: **{{ $order->code }}**
</x-mail::panel>

Đơn hàng được xác nhận sau khi chúng tôi nhận được thanh toán.
@endif

@if ($orderUrl)
<x-mail::button :url="$orderUrl">
Xem đơn hàng
</x-mail::button>
@else
Cần hỗ trợ về đơn hàng? Gọi hotline **{{ $hotline }}** và đọc mã đơn **{{ $order->code }}**.
@endif

Trân trọng,<br>
Mộc Lam — [{{ $shopUrl }}]({{ $shopUrl }})
</x-mail::message>
