{{--
    Trạng thái trống (DESIGN.md §9): một câu nói thật (Cormorant), một câu hướng dẫn, MỘT hành động.
    Slot mặc định (tuỳ chọn) cho hình vẽ SVG phẳng; không minh hoạ hoạt hình, không emoji.
--}}
@props(['title', 'text' => null, 'actionUrl' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    @if (trim($slot) !== '')
        <div class="empty-state__art" aria-hidden="true">{{ $slot }}</div>
    @endif
    <p class="empty-state__title t-title">{{ $title }}</p>
    @if ($text)
        <p class="empty-state__text measure">{{ $text }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel }}</a>
    @endif
</div>
