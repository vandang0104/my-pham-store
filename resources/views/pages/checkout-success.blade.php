{{-- Xác nhận đặt hàng (chỉ chủ đơn hoặc phiên vừa đặt xem được — CheckoutController@success). --}}
@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
    <div class="container py-6">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="t-title mb-3">Cảm ơn bạn đã đặt hàng!</h1>
                <p class="lead mb-5">Mã đơn hàng: <strong data-order-code>{{ $order->code }}</strong>
                    · <span class="badge text-bg-secondary">{{ $order->status->label() }}</span></p>

                @if ($bank)
                    <div class="alert alert-info mb-5" role="note">
                        <h2 class="h6 mb-3">Thông tin chuyển khoản</h2>
                        <p class="mb-1">Ngân hàng: {{ $bank['name'] }}</p>
                        <p class="mb-1">Số tài khoản: <strong>{{ $bank['number'] }}</strong></p>
                        <p class="mb-1">Chủ tài khoản: {{ $bank['holder'] }}</p>
                        <p class="mb-1">Số tiền: <strong>{{ $money['total'] }}</strong></p>
                        <p class="mb-0">Nội dung chuyển khoản: <strong>{{ $order->code }}</strong></p>
                    </div>
                @endif

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

                <div class="card card-body mb-5">
                    <h2 class="h5 mb-3">Giao tới</h2>
                    <p class="mb-1"><strong>{{ $order->receiver_name }}</strong> · {{ $order->phone }}</p>
                    <p class="mb-1">{{ $order->full_address }}</p>
                    <p class="mb-0 text-muted">Thanh toán: {{ $order->payment_method->label() }}@if ($order->email) · Email xác nhận đã gửi tới {{ $order->email }}@endif</p>
                </div>

                <a href="{{ route('products.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
@endsection
