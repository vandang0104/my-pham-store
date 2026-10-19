<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;

/**
 * Một dòng giỏ hàng khi đọc ra: sản phẩm (đã nạp images, brand), số lượng, giá hiện hành và thành tiền.
 * Giá không lưu trong giỏ — luôn tính lại từ sản phẩm (quy tắc 1).
 */
final readonly class CartLine
{
    public int $unitPrice;

    public int $lineTotal;

    /** Thành tiền đã định dạng VND để hiển thị */
    public string $formattedTotal;

    public function __construct(public Product $product, public int $quantity)
    {
        $this->unitPrice = $product->current_price;
        $this->lineTotal = $this->unitPrice * $quantity;
        $this->formattedTotal = Money::format($this->lineTotal);
    }
}
