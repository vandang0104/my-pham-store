{{-- Trang quản trị tạm; layout admin thật làm ở giai đoạn quản trị --}}
@extends('layouts.auth')

@section('title', 'Trang quản trị')

@section('content')
    <p class="mb-1">Xin chào, <strong>{{ $user->name }}</strong></p>
    <p class="text-secondary">Vai trò: {{ $user->role->label() }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-dark">Đăng xuất</button>
    </form>
@endsection
