{{--
    Tiêu đề section (DESIGN.md §4, §9): tiêu đề Cormorant + câu dẫn tuỳ chọn + một link chữ tuỳ chọn ("Xem tất cả").
    Không eyebrow, không đánh số. $level: 2 (mặc định) hoặc 3.
--}}
@props(['title', 'level' => 2, 'lead' => null, 'linkUrl' => null, 'linkLabel' => null, 'id' => null])

<div {{ $attributes->merge(['class' => 'section-heading']) }}>
    <div class="section-heading__text">
        @if ((int) $level === 3)
            <h3 class="t-title section-heading__title" @if ($id) id="{{ $id }}" @endif>{{ $title }}</h3>
        @else
            <h2 class="section-heading__title" @if ($id) id="{{ $id }}" @endif>{{ $title }}</h2>
        @endif
        @if ($lead)
            <p class="section-heading__lead measure">{{ $lead }}</p>
        @endif
    </div>
    @if ($linkUrl)
        <a href="{{ $linkUrl }}" class="section-heading__link link-nav">
            {{ $linkLabel ?? 'Xem tất cả' }} <x-icon name="arrow-right" :size="20" />
        </a>
    @endif
</div>
