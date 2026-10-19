{{-- Trang giữ chỗ cho các mục quản trị chưa làm --}}
@extends('layouts.auth')

@section('title', $title)

@section('content')
    <p class="text-secondary">Chức năng đang được xây dựng.</p>
    <a href="{{ route('admin.dashboard') }}">Về trang quản trị</a>
@endsection
