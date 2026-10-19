{{-- Trang giữ chỗ cho các mục quản trị chưa làm --}}
@extends('layouts.admin')

@section('title', $title)

@section('content')
    <p class="text-muted">Chức năng đang được xây dựng.</p>
    <a href="{{ route('admin.dashboard') }}">Về trang quản trị</a>
@endsection
