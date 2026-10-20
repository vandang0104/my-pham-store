<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Product;
use App\Services\CartService;
use App\Support\Money;
use App\Support\Shipping;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Giỏ hàng: form POST + redirect + flash (không AJAX). Lỗi nghiệp vụ (hết hàng, vượt kho) là CartException,
 * tự render về trang trước kèm flash lỗi.
 */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        $items = $this->cart->items();

        // Dòng bị bỏ / hạ số lượng khi đọc giỏ: báo ngay trên trang này
        if ($notices = $this->cart->notices()) {
            session()->now('info', implode(' ', $notices));
        }

        $subtotal = $this->cart->subtotal($items);
        $remaining = Shipping::remainingForFree($subtotal);

        return view('pages.cart', [
            'items' => $items,
            'formattedSubtotal' => Money::format($subtotal),
            'freeShippingRemaining' => $remaining > 0 ? Money::format($remaining) : null,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        // withTrashed: sản phẩm đã xoá mềm báo "không còn bán" (CartService) thay vì trang 404
        $product = Product::withTrashed()->findOrFail($request->validated('product_id'));
        $this->cart->add($product, $request->quantity());

        return back()->with('status', __('cart.added', ['name' => $product->name]));
    }

    public function update(UpdateCartRequest $request, Product $product): RedirectResponse
    {
        $this->cart->updateQuantity($product, (int) $request->validated('quantity'));

        return redirect()->route('cart.index')->with('status', __('cart.updated', ['name' => $product->name]));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('cart.index')->with('status', __('cart.removed', ['name' => $product->name]));
    }
}
