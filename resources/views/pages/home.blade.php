{{-- Trang chủ (bản rút gọn): hero banner → nổi bật → danh mục → đang ưu đãi → giới thiệu cửa hàng → footer --}}
@extends('layouts.app')

@section('content')
    @include('pages.home.hero')
    @include('pages.home.featured')
    @include('pages.home.categories')
    @include('pages.home.on-sale')
    @include('pages.home.about')
@endsection
