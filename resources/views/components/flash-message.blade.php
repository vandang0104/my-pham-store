{{--
    Một dải thông báo (DESIGN.md §9 Flash): lỗi → role="alert", không tự ẩn; thành công/thông tin → role="status", tự ẩn sau 6 giây.
    <x-flash> đọc session rồi dùng component này; trang styleguide dùng trực tiếp.
--}}
@props(['type' => 'info', 'message', 'autohide' => null])

<div {{ $attributes->class(['flash', 'flash--'.$type]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}" data-flash @if ($autohide ?? $type !== 'error') data-flash-autohide @endif>
    <div class="container flash__inner">
        <p class="flash__text">{{ $message }}</p>
        <button type="button" class="flash__close" data-flash-close aria-label="Đóng thông báo">
            <x-icon name="close" :size="20" />
        </button>
    </div>
</div>
