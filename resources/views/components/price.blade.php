<p {{ $attributes->merge(['class' => 'price price--'.$size]) }}>
    @if ($onSale)
        <span class="visually-hidden">Giá khuyến mãi</span>
        <span class="price__current price__current--sale">{{ $current }}</span>
        <span class="visually-hidden">, giá gốc</span>
        <del class="price__original">{{ $original }}</del>
    @else
        <span class="price__current">{{ $current }}</span>
    @endif
</p>
