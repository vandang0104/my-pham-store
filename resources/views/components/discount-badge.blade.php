{{--
    Nhãn phần trăm giảm "−20%" (DESIGN.md §9 Badge): nền đỏ son, chữ lụa, góc vuông, 13px.
    Mặc định aria-hidden vì <x-price> đã đọc giá khuyến mãi và giá gốc; đặt :decorative="false" khi đứng một mình.
--}}
@props(['percent', 'decorative' => true])

<span {{ $attributes->class(['discount-badge']) }} @if ($decorative) aria-hidden="true" @endif>−{{ $percent }}%</span>
