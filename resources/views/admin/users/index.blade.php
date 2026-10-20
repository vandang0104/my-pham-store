@extends('layouts.admin')

@section('title', 'Tài khoản')

@section('content')
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="q" class="form-label">Tìm tên / email</label>
            <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <label for="vai-tro" class="form-label">Vai trò</label>
            <select id="vai-tro" name="vai-tro" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($filters['vai-tro'] === $role)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-outline-secondary">Lọc</button></div>
    </form>

    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th class="text-end">Số đơn</th><th>Trạng thái</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->role->label() }}</td>
                            <td class="text-end">{{ $user->orders_count }}</td>
                            <td><span @class(['badge', 'text-bg-success' => $user->is_active, 'text-bg-danger' => ! $user->is_active])>{{ $user->is_active ? 'Hoạt động' : 'Đã khoá' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Không có tài khoản phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $users->links() }}
@endsection
