{{-- Trang quản trị tạm; layout admin thật làm ở giai đoạn quản trị --}}
@extends('layouts.admin')

@section('title', 'Trang quản trị')

@section('content')
    <p class="mb-1">Xin chào, <strong>{{ $user->name }}</strong></p>
    <p class="text-muted">Vai trò: {{ $user->role->label() }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-text">Đăng xuất</button>
    </form>
@endsection
