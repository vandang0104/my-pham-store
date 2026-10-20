@extends('account.layout')

@section('title', 'Đơn hàng của tôi')

@section('account')
    <form method="GET" action="{{ route('account.orders.index') }}" class="row g-3 align-items-end mb-5">
        <div class="col-sm-6 col-md-4">
            <label for="trang-thai" class="form-label">Trạng thái</label>
            <select id="trang-thai" name="trang-thai" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Lọc</button>
        </div>
    </form>

    @if ($orders->isEmpty())
        <x-empty-state :title="$currentStatus ? 'Không có đơn ở trạng thái này' : 'Bạn chưa có đơn hàng nào'"
            text="Khám phá sản phẩm và đặt đơn đầu tiên." :action-url="route('products.index')" action-label="Xem sản phẩm" />
    @else
        @include('account.orders._table')
        {{ $orders->links() }}
    @endif
@endsection
