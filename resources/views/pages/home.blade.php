{{-- Trang chủ TẠM (bước 3.3): chỉ khung layout. Nội dung thật theo DESIGN.md §10.1 làm ở bước 4.1. --}}
@extends('layouts.app')

@section('content')
    <section class="home-intro" aria-labelledby="home-title">
        <div class="container">
            <h1 id="home-title" class="t-display home-intro__title">Mỹ phẩm thảo mộc Việt</h1>
            <p class="t-lead measure home-intro__lead">Chăm sóc da, trang điểm và nước hoa từ nguyên liệu Đà Lạt, Hội An, Đắk Lắk — ghi rõ thành phần và nơi trồng.</p>
            <a href="{{ url('/san-pham') }}" class="btn btn-primary">Xem sản phẩm</a>
        </div>
    </section>
@endsection
