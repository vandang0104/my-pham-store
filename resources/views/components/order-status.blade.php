{{-- Badge trạng thái đơn (màu theo OrderStatus::badgeClass()) --}}
@props(['status'])

<span {{ $attributes->class(['badge', $status->badgeClass()]) }}>{{ $status->label() }}</span>
