{{-- Giỏ hàng (Bootstrap mặc định): bảng dòng giỏ, cập nhật / xoá từng dòng, tạm tính, nút đặt hàng. --}}
@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <div class="container py-6">
        <x-breadcrumb :items="[['label' => 'Giỏ hàng']]" class="mb-4" />
        <h1 class="t-title mb-5">Giỏ hàng</h1>

        @if ($items->isEmpty())
            <x-empty-state title="Giỏ hàng của bạn đang trống" text="Xem các sản phẩm đang ưu đãi hoặc tìm sản phẩm bạn cần."
                :action-url="route('products.index')" action-label="Xem sản phẩm" />
        @else
            <div class="table-responsive mb-5">
                <table class="table align-middle cart-table">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Đơn giá</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col" class="text-end">Thành tiền</th>
                            <th scope="col"><span class="visually-hidden">Xoá</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($item->product->primary_image)
                                            <img src="{{ $item->product->primary_image->url }}" alt="{{ $item->product->name }}" width="64" height="80" class="object-fit-cover cart-table__thumb" loading="lazy">
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $item->product) }}" class="fw-medium">{{ $item->product->name }}</a>
                                            <div class="small text-muted">{{ $item->product->brand?->name }} · Còn {{ $item->product->stock }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><x-price :product="$item->product" size="sm" class="mb-0" /></td>
                                <td>
                                    <form method="POST" action="{{ route('cart.update', $item->product->id) }}" class="d-flex gap-2 align-items-center">
                                        @csrf
                                        @method('PATCH')
                                        <label for="sl-{{ $item->product->id }}" class="visually-hidden">Số lượng {{ $item->product->name }}</label>
                                        <input id="sl-{{ $item->product->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}"
                                            class="form-control form-control-sm cart-table__qty" required>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cập nhật</button>
                                    </form>
                                </td>
                                <td class="text-end fw-medium tnum">{{ $item->formattedTotal }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('cart.destroy', $item->product->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger" aria-label="Xoá {{ $item->product->name }} khỏi giỏ">Xoá</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-6 col-lg-4">
                    <div class="card card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính</span>
                            <strong class="tnum" data-cart-subtotal>{{ $formattedSubtotal }}</strong>
                        </div>
                        <p class="small text-muted mb-4">Phí giao hàng tính ở bước đặt hàng.</p>
                        {{-- Trang thanh toán làm ở S3 --}}
                        <a href="{{ url('/thanh-toan') }}" class="btn btn-primary w-100 mb-3">Tiến hành đặt hàng</a>
                        <a href="{{ route('products.index') }}" class="text-center">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
