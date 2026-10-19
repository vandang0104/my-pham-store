{{-- Trang chủ (DESIGN.md §10.1): 1 hero → 2 nổi bật → 3 danh mục → 4 đang ưu đãi → 5 câu chuyện → 6 lời mời → footer --}}
@extends('layouts.app')

@section('content')
    @include('pages.home.hero')
    @include('pages.home.featured')
    @include('pages.home.categories')
    @include('pages.home.on-sale')
    @include('pages.home.story')
    @include('pages.home.invite')
@endsection
