<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderException;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Shipping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Đặt hàng (quy tắc 2, 4, 7): một transaction, khoá dòng sản phẩm theo id tăng dần (tránh deadlock),
 * kiểm lại tồn kho, snapshot order_items, trừ kho, ghi lịch sử trạng thái; sau commit thì xoá giỏ và gửi mail.
 * Mã giảm giá đã cắt: coupon_id null, discount 0.
 */
class OrderService
{
    /** Bảng chữ của phần đuôi mã đơn (bỏ 0/O/1/I dễ nhầm) */
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(private readonly CartService $cart) {}

    /**
     * @param  array{receiver_name: string, phone: string, email: ?string, province: string, district: ?string, ward: string, detail: string, note: ?string, payment_method: string}  $data
     *
     * @throws OrderException
     */
    public function placeOrder(array $data, ?User $user): Order
    {
        $lines = $this->cart->items();
        if ($notices = $this->cart->notices()) {
            // Giỏ vừa bị điều chỉnh (SP ngừng bán / thiếu kho): khách phải xem lại trước khi đặt
            throw OrderException::because('cart_changed', ['details' => implode(' ', $notices)]);
        }
        if ($lines->isEmpty()) {
            throw OrderException::because('empty_cart');
        }

        $quantities = $lines->mapWithKeys(fn (CartLine $line) => [$line->product->id => $line->quantity])->all();

        $order = DB::transaction(function () use ($quantities, $data, $user) {
            // Khoá theo thứ tự id tăng dần để hai đơn đồng thời không khoá chéo nhau
            $products = Product::query()
                ->whereKey(array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->ensureStock($products, $quantities);

            $items = collect($quantities)->map(function (int $quantity, int $productId) use ($products) {
                $product = $products[$productId];

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->current_price,
                    'quantity' => $quantity,
                    'line_total' => $product->current_price * $quantity,
                ];
            })->values();

            $subtotal = $items->sum('line_total');
            $shippingFee = Shipping::fee($subtotal);

            $order = Order::query()->forceCreate([
                'code' => $this->generateCode(),
                'user_id' => $user?->id,
                'coupon_id' => null,
                'receiver_name' => $data['receiver_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? $user?->email,
                'province' => $data['province'],
                'district' => $data['district'] ?? null,
                'ward' => $data['ward'],
                'detail' => $data['detail'],
                'payment_method' => PaymentMethod::from($data['payment_method']),
                'payment_status' => PaymentStatus::Unpaid,
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->createMany($items->all());
            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::Pending,
                'changed_by' => $user?->id,
                'note' => __('order.history_placed'),
            ]);

            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $product->forceFill([
                    'stock' => $product->stock - $item['quantity'],
                    'sold_count' => $product->sold_count + $item['quantity'],
                ])->save();
            }

            return $order;
        });

        // Sau commit: giỏ session không rollback được nên chỉ xoá khi đơn đã chắc chắn tạo xong
        $this->cart->clear();
        $this->sendConfirmation($order);

        return $order;
    }

    /**
     * @param  Collection<int, Product>  $products  đã khoá dòng
     * @param  array<int, int>  $quantities
     *
     * @throws OrderException
     */
    private function ensureStock($products, array $quantities): void
    {
        $short = [];
        foreach ($quantities as $productId => $quantity) {
            $product = $products->get($productId);
            if ($product === null || ! $product->is_active || $product->stock < $quantity) {
                $short[] = __('order.out_of_stock_item', ['name' => $product?->name ?? '#'.$productId, 'stock' => $product?->stock ?? 0]);
            }
        }

        if ($short !== []) {
            // Ném trong transaction → rollback toàn bộ, kho không đổi
            throw OrderException::because('out_of_stock', ['items' => implode(', ', $short)]);
        }
    }

    /** MP-YYYYMMDD-XXXX, thử lại nếu trùng (cột code unique là chốt chặn cuối) */
    private function generateCode(): string
    {
        do {
            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
            $code = 'MP-'.now()->format('Ymd').'-'.$suffix;
        } while (Order::query()->where('code', $code)->exists());

        return $code;
    }

    /** Gửi mail xác nhận; lỗi gửi mail không làm hỏng đơn đã tạo — ghi log (report) để xử lý sau */
    private function sendConfirmation(Order $order): void
    {
        if (! $order->email) {
            return;
        }

        rescue(fn () => Mail::to($order->email)->send(new OrderPlaced($order)), report: true);
    }
}
