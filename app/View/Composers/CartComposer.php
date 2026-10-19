<?php

namespace App\View\Composers;

use App\Services\CartService;
use Illuminate\View\View;

/**
 * Số sản phẩm trong giỏ (tổng số lượng) cho badge ở header — 1 truy vấn với user, 0 với khách vãng lai.
 */
class CartComposer
{
    public function __construct(private readonly CartService $cart) {}

    public function compose(View $view): void
    {
        $view->with('cartCount', $this->cart->count());
    }
}
