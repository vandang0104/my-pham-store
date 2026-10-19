<?php

namespace App\View\Composers;

use Illuminate\View\View;

/**
 * Số sản phẩm trong giỏ cho badge ở header.
 * TẠM trả 0 cho tới bước S2 (CartService) — khi đó đọc từ CartService::count().
 */
class CartComposer
{
    public function compose(View $view): void
    {
        $view->with('cartCount', 0);
    }
}
