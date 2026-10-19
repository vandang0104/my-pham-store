<nav {{ $attributes->merge(['class' => 'breadcrumb-nav']) }} aria-label="Breadcrumb">
    <ol class="breadcrumb">
        @foreach ($trail as $i => $item)
            @if ($isLast($i))
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
<script type="application/ld+json">{!! $jsonLd() !!}</script>
