@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <a href="{{ route('admin.users.index') }}" class="small d-inline-block mb-4">← Danh sách tài khoản</a>

    <div class="row g-5">
        <div class="col-lg-5">
            <div class="card card-body mb-5">
                <dl class="row mb-0">
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $user->email }}</dd>
                    <dt class="col-5">SĐT</dt><dd class="col-7">{{ $user->phone ?? '—' }}</dd>
                    <dt class="col-5">Vai trò</dt><dd class="col-7">{{ $user->role->label() }}</dd>
                    <dt class="col-5">Trạng thái</dt><dd class="col-7">{{ $user->is_active ? 'Hoạt động' : 'Đã khoá' }}</dd>
                    <dt class="col-5">Ngày tạo</dt><dd class="col-7">{{ $user->created_at?->format('d/m/Y') }}</dd>
                    <dt class="col-5">Số đơn</dt><dd class="col-7" data-orders-count>{{ $ordersCount }}</dd>
                    <dt class="col-5">Tổng chi tiêu</dt><dd class="col-7 mb-0" data-total-spent>{{ $totalSpent }}</dd>
                </dl>
            </div>

            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="card card-body mb-4">
                @csrf
                @method('PATCH')
                <label for="role" class="form-label">Đổi vai trò</label>
                <div class="d-flex gap-2">
                    <select id="role" name="role" class="form-select">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-primary">Lưu</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.users.toggle', $user) }}"
                onsubmit="return confirm('{{ $user->is_active ? 'Khoá' : 'Mở khoá' }} tài khoản {{ $user->name }}?');">
                @csrf
                @method('PATCH')
                <button type="submit" @class(['btn w-100', 'btn-outline-danger' => $user->is_active, 'btn-outline-success' => ! $user->is_active])>
                    {{ $user->is_active ? 'Khoá tài khoản' : 'Mở khoá tài khoản' }}
                </button>
            </form>
        </div>

        <div class="col-lg-7">
            <div class="card card-body">
                <h2 class="h5 mb-3">Đơn gần đây</h2>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Mã đơn</th><th>Ngày</th><th>Trạng thái</th><th class="text-end">Tổng</th></tr></thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->code }}</a></td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td><x-order-status :status="$order->status" /></td>
                                    <td class="text-end">{{ $order->formatted_total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Chưa có đơn.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
