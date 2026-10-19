{{-- Badge số trên túi hàng (DESIGN.md §9): ẩn khi 0, trên 99 hiện "99+", có chữ ẩn cho trình đọc màn hình --}}
@props(['count' => 0])

@if ($count > 0)
    <span {{ $attributes->merge(['class' => 'cart-badge']) }}>
        <span aria-hidden="true">{{ $count > 99 ? '99+' : $count }}</span>
        <span class="visually-hidden">{{ $count }} sản phẩm trong giỏ</span>
    </span>
@endif
