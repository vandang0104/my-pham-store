@if ($hasReviews())
    <p {{ $attributes->merge(['class' => 'rating']) }}>
        <span class="rating__stars" role="img" aria-label="Đánh giá {{ $score }} trên 5">
            @foreach ($stars as $star)
                <x-icon :name="$star" :size="16" />
            @endforeach
        </span>
        <span class="rating__score" aria-hidden="true">{{ $score }}</span>
        <span class="rating__count">({{ $count }} đánh giá)</span>
    </p>
@else
    <p {{ $attributes->merge(['class' => 'rating rating--empty']) }}>Chưa có đánh giá</p>
@endif
