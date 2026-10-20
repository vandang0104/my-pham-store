{{-- Trang lỗi thường gặp (404, 403, 419, 429): giữ header/footer của cửa hàng để khách đi tiếp được --}}
@extends('layouts.app')

@section('content')
    <div class="container py-9">
        <x-empty-state :title="$title" :text="$text" :action-url="route('home')" action-label="Về trang chủ" />
    </div>
@endsection
