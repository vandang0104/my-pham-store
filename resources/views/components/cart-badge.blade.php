{{-- Badge số trên túi hàng (DESIGN.md §9): ẩn khi 0, từ 10 giãn ngang, trên 99 hiện "99+", có chữ ẩn cho trình đọc màn hình --}}
@props(['count' => 0])

@if ($count > 0)
    <span {{ $attributes->class(['cart-badge', 'cart-badge--wide' => $count >= 10]) }}>
        <span aria-hidden="true">{{ $count > 99 ? '99+' : $count }}</span>
        <span class="visually-hidden">{{ $count }} sản phẩm trong giỏ</span>
    </span>
@endif
