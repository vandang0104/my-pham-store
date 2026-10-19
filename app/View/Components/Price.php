<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Giá sản phẩm (DESIGN.md §9) — dùng accessor của Product (quy tắc 1: giá hiện hành).
 * Có khuyến mãi: giá KM đỏ son + giá gốc gạch ngang + câu đọc đầy đủ cho trình đọc màn hình.
 */
class Price extends Component
{
    public bool $onSale;

    public string $current;

    public string $original;

    public function __construct(public Product $product, public string $size = 'md')
    {
        $this->onSale = $product->is_on_sale;
        $this->current = $product->formatted_price;
        $this->original = $product->formatted_original_price;
    }

    public function render(): View
    {
        return view('components.price');
    }
}
