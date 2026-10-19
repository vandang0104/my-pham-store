{{--
    Trang xác thực (đăng nhập, đăng ký, quên/đặt lại mật khẩu): dùng layout chung của trang khách
    (header, flash, footer), nội dung là một tấm panel hẹp ở giữa.
    Trang con khai báo @section('title') và @section('form').
--}}
@extends('layouts.app')

@section('content')
    <div class="container section-sm">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="panel">
                    <h1 class="mb-5">@yield('title')</h1>
                    @yield('form')
                </div>
            </div>
        </div>
    </div>
@endsection
