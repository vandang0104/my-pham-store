<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Giỏ hàng (quy tắc 8): khách vãng lai lưu trong session ([product_id => số lượng]), user đăng nhập lưu bảng
 * carts/cart_items. Khi đăng nhập/đăng ký, listener MergeGuestCart gộp giỏ session vào DB.
 * Ràng buộc: 1 ≤ số lượng ≤ tồn kho; không thêm sản phẩm ẩn/xoá mềm/hết hàng; giá luôn lấy giá hiện hành.
 */
class CartService
{
    public const SESSION_KEY = 'cart';

    /** @var list<string> Thông báo điều chỉnh của lần đọc giỏ gần nhất (bỏ dòng, hạ số lượng) */
    private array $notices = [];

    public function __construct(private readonly Guard $auth, private readonly Session $session) {}

    /**
     * Thêm sản phẩm (cộng dồn nếu đã có). Vượt tồn kho thì chặn, giỏ giữ nguyên.
     *
     * @throws CartException
     */
    public function add(Product $product, int $quantity = 1): void
    {
        $this->ensureAvailable($product);
        $inCart = $this->lines()[$product->id] ?? 0;
        $this->ensureStock($product, $inCart + $quantity, $inCart);

        $this->put($product->id, $inCart + $quantity);
    }

    /**
     * Đặt lại số lượng một dòng đã có trong giỏ.
     *
     * @throws CartException
     */
    public function updateQuantity(Product $product, int $quantity): void
    {
        if (! isset($this->lines()[$product->id])) {
            throw CartException::because('not_in_cart');
        }
        $this->ensureAvailable($product);
        $this->ensureStock($product, $quantity, 0);

        $this->put($product->id, $quantity);
    }

    public function remove(Product $product): void
    {
        $this->put($product->id, null);
    }

    public function clear(): void
    {
        $user = $this->user();
        if ($user === null) {
            $this->session->forget(self::SESSION_KEY);

            return;
        }

        $user->cart()->first()?->items()->delete();
    }

    /**
     * Các dòng giỏ đã chuẩn hoá: bỏ sản phẩm ẩn/xoá mềm/hết hàng, hạ số lượng vượt tồn kho (lưu lại thay đổi
     * và ghi thông báo vào notices()). 1 truy vấn sản phẩm + eager load images, brand.
     *
     * @return Collection<int, CartLine>
     */
    public function items(): Collection
    {
        $this->notices = [];
        $lines = $this->lines();
        if ($lines === []) {
            return collect();
        }

        // withTrashed để biết tên sản phẩm đã xoá mềm khi báo cho khách
        $products = Product::withTrashed()->whereKey(array_keys($lines))->with(['images', 'brand'])->get()->keyBy('id');

        $items = collect();
        foreach ($lines as $productId => $quantity) {
            $product = $products->get($productId);

            if ($product === null || $product->trashed() || ! $product->is_active || $product->stock <= 0) {
                $this->put($productId, null);
                $this->notices[] = __('cart.adjusted_removed', ['name' => $product?->name ?? __('cart.unknown_product')]);

                continue;
            }

            if ($quantity > $product->stock) {
                $quantity = $product->stock;
                $this->put($productId, $quantity);
                $this->notices[] = __('cart.adjusted_quantity', ['name' => $product->name, 'stock' => $quantity]);
            }

            $items->push(new CartLine($product, $quantity));
        }

        return $items;
    }

    /** @return list<string> */
    public function notices(): array
    {
        return $this->notices;
    }

    /** Tổng số lượng (badge header) — đọc thẳng dữ liệu đã lưu, không chuẩn hoá */
    public function count(): int
    {
        return array_sum($this->lines());
    }

    /**
     * Tạm tính theo giá hiện hành.
     *
     * @param  Collection<int, CartLine>|null  $items  truyền sẵn để khỏi đọc lại giỏ
     */
    public function subtotal(?Collection $items = null): int
    {
        return ($items ?? $this->items())->sum('lineTotal');
    }

    /**
     * Gộp giỏ session vào giỏ DB của user (gọi khi đăng nhập / đăng ký): cộng dồn, cắt theo tồn kho,
     * bỏ sản phẩm ẩn/hết hàng; xong thì xoá giỏ session.
     */
    public function mergeGuestCart(User $user): void
    {
        $guest = $this->sessionLines();
        if ($guest === []) {
            return;
        }

        $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
        $existing = $cart->items()->pluck('quantity', 'product_id')->all();
        $products = Product::query()->active()->where('stock', '>', 0)->whereKey(array_keys($guest))->get(['id', 'stock']);

        foreach ($products as $product) {
            $quantity = min(($existing[$product->id] ?? 0) + $guest[$product->id], $product->stock);
            $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $quantity]);
        }

        $this->session->forget(self::SESSION_KEY);
    }

    /** @throws CartException */
    private function ensureAvailable(Product $product): void
    {
        if (! $product->is_active || $product->trashed()) {
            throw CartException::because('unavailable');
        }
        if ($product->stock <= 0) {
            throw CartException::because('out_of_stock', ['name' => $product->name]);
        }
    }

    /** @throws CartException */
    private function ensureStock(Product $product, int $wanted, int $inCart): void
    {
        if ($wanted > $product->stock) {
            throw CartException::because('exceeds_stock', ['name' => $product->name, 'stock' => $product->stock, 'in_cart' => $inCart]);
        }
    }

    /** @return array<int, int> product_id => số lượng */
    private function lines(): array
    {
        $user = $this->user();
        if ($user === null) {
            return $this->sessionLines();
        }

        return $user->cart()->first()?->items()->pluck('quantity', 'product_id')->map(fn ($q) => (int) $q)->all() ?? [];
    }

    /** @return array<int, int> */
    private function sessionLines(): array
    {
        $lines = $this->session->get(self::SESSION_KEY, []);

        return is_array($lines) ? array_map('intval', $lines) : [];
    }

    /** Ghi số lượng một dòng; null = xoá dòng */
    private function put(int $productId, ?int $quantity): void
    {
        $user = $this->user();

        if ($user === null) {
            $lines = $this->sessionLines();
            if ($quantity === null) {
                unset($lines[$productId]);
            } else {
                $lines[$productId] = $quantity;
            }
            $this->session->put(self::SESSION_KEY, $lines);

            return;
        }

        if ($quantity === null) {
            $user->cart()->first()?->items()->where('product_id', $productId)->delete();

            return;
        }

        Cart::query()->firstOrCreate(['user_id' => $user->id])
            ->items()->updateOrCreate(['product_id' => $productId], ['quantity' => $quantity]);
    }

    private function user(): ?User
    {
        $user = $this->auth->user();

        return $user instanceof User ? $user : null;
    }
}
