<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Factories\Support\Vn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;

/**
 * 15 đơn mẫu đủ các trạng thái: snapshot order_items, nhật ký trạng thái theo luồng hợp lệ,
 * total = subtotal - discount + shipping_fee. Sau đó đếm lại used_count của coupon và sold_count sản phẩm.
 */
class OrderSeeder extends Seeder
{
    /** Quy tắc phí ship dùng cho dữ liệu mẫu (sau này đọc từ settings) */
    public const SHIPPING_FEE = 30000;

    public const FREE_SHIPPING_FROM = 500000;

    /** khach: thứ tự khách hàng (null = khách vãng lai), ngay: số ngày trước, so_sp: số sản phẩm khác nhau, huy_tu: trạng thái lúc huỷ */
    private const PLAN = [
        ['status' => OrderStatus::Delivered, 'khach' => 1, 'tt' => 'cod', 'coupon' => 'DEPXINH10', 'ngay' => 50, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 2, 'tt' => 'bank_transfer', 'coupon' => null, 'ngay' => 44, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 3, 'tt' => 'cod', 'coupon' => 'FLASH20', 'ngay' => 38, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 4, 'tt' => 'cod', 'coupon' => null, 'ngay' => 30, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 5, 'tt' => 'bank_transfer', 'coupon' => 'FLASH20', 'ngay' => 24, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 6, 'tt' => 'cod', 'coupon' => 'VIP25', 'ngay' => 18, 'so_sp' => 5],
        ['status' => OrderStatus::Delivered, 'khach' => 7, 'tt' => 'cod', 'coupon' => null, 'ngay' => 12, 'so_sp' => 5],
        ['status' => OrderStatus::Shipping, 'khach' => 8, 'tt' => 'cod', 'coupon' => 'GIAM50K', 'ngay' => 3, 'so_sp' => 3],
        ['status' => OrderStatus::Shipping, 'khach' => null, 'tt' => 'bank_transfer', 'coupon' => null, 'ngay' => 2, 'so_sp' => 2],
        ['status' => OrderStatus::Confirmed, 'khach' => 9, 'tt' => 'bank_transfer', 'coupon' => null, 'ngay' => 2, 'so_sp' => 2],
        ['status' => OrderStatus::Confirmed, 'khach' => 10, 'tt' => 'cod', 'coupon' => 'DEPXINH10', 'ngay' => 1, 'so_sp' => 3],
        ['status' => OrderStatus::Pending, 'khach' => null, 'tt' => 'cod', 'coupon' => null, 'ngay' => 0, 'so_sp' => 1],
        ['status' => OrderStatus::Pending, 'khach' => 11, 'tt' => 'bank_transfer', 'coupon' => null, 'ngay' => 0, 'so_sp' => 2],
        ['status' => OrderStatus::Cancelled, 'khach' => 12, 'tt' => 'cod', 'coupon' => 'DEPXINH10', 'ngay' => 9, 'so_sp' => 3, 'huy_tu' => OrderStatus::Pending],
        ['status' => OrderStatus::Cancelled, 'khach' => null, 'tt' => 'bank_transfer', 'coupon' => null, 'ngay' => 6, 'so_sp' => 2, 'huy_tu' => OrderStatus::Confirmed],
    ];

    private const NOTES = [null, null, null, 'Giao giờ hành chính giúp mình.', 'Gói quà giúp mình nhé, cảm ơn shop!', 'Gọi trước khi giao 15 phút.'];

    public function run(): void
    {
        $customers = User::query()->where('role', UserRole::Customer)->orderBy('id')->with('addresses')->get();
        $staffIds = User::query()->whereIn('role', [UserRole::Admin, UserRole::Staff])->pluck('id')->all();
        $products = Product::query()->get();
        $coupons = Coupon::query()->get()->keyBy('code');

        foreach (self::PLAN as $i => $row) {
            $customer = $row['khach'] === null ? null : $customers[$row['khach'] - 1];
            $this->createOrder($i + 1, $row, $customer, $products, $coupons, $staffIds);
        }

        // Lượt dùng coupon = số đơn chưa huỷ (huỷ đơn đã hoàn lượt — quy tắc 3)
        foreach ($coupons as $coupon) {
            $coupon->forceFill(['used_count' => $coupon->orders()->where('status', '!=', OrderStatus::Cancelled)->count()])->save();
        }

        // Đã bán = tổng số lượng trong các đơn đã giao
        OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Delivered))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id')
            ->each(fn ($qty, $productId) => Product::query()->whereKey($productId)->update(['sold_count' => (int) $qty]));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  Collection<int, Product>  $products
     * @param  Collection<string, Coupon>  $coupons
     * @param  list<int>  $staffIds
     */
    private function createOrder(int $seq, array $row, ?User $customer, Collection $products, Collection $coupons, array $staffIds): void
    {
        /** @var OrderStatus $status */
        $status = $row['status'];
        $method = PaymentMethod::from($row['tt']);
        $placedAt = now()->subDays($row['ngay'])->subMinutes(fake()->numberBetween(60, 600));

        // Snapshot tên/sku/giá hiện hành tại thời điểm đặt
        $items = collect(fake()->randomElements($products->all(), $row['so_sp']))->map(function (Product $product) {
            $quantity = fake()->boolean(80) ? 1 : 2;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->current_price,
                'quantity' => $quantity,
                'line_total' => $product->current_price * $quantity,
            ];
        });

        $subtotal = $items->sum('line_total');
        $coupon = $row['coupon'] === null ? null : $coupons[$row['coupon']];
        if ($coupon !== null && $subtotal < $coupon->min_order) {
            $coupon = null;
        }
        $discount = $coupon === null ? 0 : $this->discount($coupon, $subtotal);
        $shippingFee = $subtotal >= self::FREE_SHIPPING_FROM ? 0 : self::SHIPPING_FEE;

        $cancelledFrom = $row['huy_tu'] ?? null;
        $confirmedAt = in_array($status, [OrderStatus::Confirmed, OrderStatus::Shipping, OrderStatus::Delivered], true) || $cancelledFrom === OrderStatus::Confirmed
            ? $placedAt->copy()->addHours(2) : null;
        $shippedAt = in_array($status, [OrderStatus::Shipping, OrderStatus::Delivered], true) ? $confirmedAt->copy()->addDay() : null;
        $deliveredAt = $status === OrderStatus::Delivered ? $shippedAt->copy()->addDays(2) : null;
        $cancelledAt = match (true) {
            $status !== OrderStatus::Cancelled => null,
            $cancelledFrom === OrderStatus::Confirmed => $confirmedAt->copy()->addDay(),
            default => $placedAt->copy()->addHours(5),
        };

        $order = Order::query()->forceCreate([
            'code' => 'DH'.$placedAt->format('ymd').sprintf('%04d', $seq),
            'user_id' => $customer?->id,
            'coupon_id' => $coupon?->id,
            ...$this->recipient($customer),
            'payment_method' => $method,
            'payment_status' => $this->paymentStatus($method, $status, $confirmedAt),
            'status' => $status,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'total' => $subtotal - $discount + $shippingFee,
            'note' => fake()->randomElement(self::NOTES),
            'confirmed_at' => $confirmedAt,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'cancelled_at' => $cancelledAt,
            'created_at' => $placedAt,
            'updated_at' => $cancelledAt ?? $deliveredAt ?? $shippedAt ?? $confirmedAt ?? $placedAt,
        ]);

        foreach ($items as $item) {
            $order->items()->forceCreate($item + ['created_at' => $placedAt, 'updated_at' => $placedAt]);
        }

        $this->createHistories($order, $customer, $staffIds, $method, compact('placedAt', 'confirmedAt', 'shippedAt', 'deliveredAt', 'cancelledAt', 'cancelledFrom'));
    }

    /**
     * Nhật ký trạng thái theo đúng luồng của OrderStatus.
     *
     * @param  list<int>  $staffIds
     * @param  array<string, mixed>  $t
     */
    private function createHistories(Order $order, ?User $customer, array $staffIds, PaymentMethod $method, array $t): void
    {
        $staff = fn () => fake()->randomElement($staffIds);
        $steps = [[null, OrderStatus::Pending, $customer?->id, $t['placedAt'], 'Khách đặt hàng.']];

        if ($t['confirmedAt'] !== null) {
            $steps[] = [OrderStatus::Pending, OrderStatus::Confirmed, $staff(), $t['confirmedAt'],
                $method === PaymentMethod::BankTransfer ? 'Đã nhận chuyển khoản, xác nhận đơn.' : 'Đã gọi xác nhận với khách.'];
        }
        if ($t['shippedAt'] !== null) {
            $steps[] = [OrderStatus::Confirmed, OrderStatus::Shipping, $staff(), $t['shippedAt'], 'Đã bàn giao cho đơn vị vận chuyển.'];
        }
        if ($t['deliveredAt'] !== null) {
            $steps[] = [OrderStatus::Shipping, OrderStatus::Delivered, $staff(), $t['deliveredAt'], 'Giao hàng thành công.'];
        }
        if ($t['cancelledAt'] !== null) {
            $steps[] = $t['cancelledFrom'] === OrderStatus::Confirmed
                ? [OrderStatus::Confirmed, OrderStatus::Cancelled, $staff(), $t['cancelledAt'], 'Khách yêu cầu huỷ, đã hoàn tiền chuyển khoản.']
                : [OrderStatus::Pending, OrderStatus::Cancelled, $customer?->id, $t['cancelledAt'], 'Khách huỷ: đặt nhầm sản phẩm.'];
        }

        foreach ($steps as [$from, $to, $by, $at, $note]) {
            if ($from !== null && ! $from->canTransitionTo($to)) {
                throw new LogicException("Luồng trạng thái không hợp lệ: {$from->value} → {$to->value}");
            }

            $order->statusHistories()->forceCreate([
                'from_status' => $from, 'to_status' => $to, 'changed_by' => $by, 'note' => $note, 'created_at' => $at,
            ]);
        }
    }

    /** percent: theo % có trần max_discount; fixed: không vượt quá tạm tính */
    private function discount(Coupon $coupon, int $subtotal): int
    {
        return $coupon->type === CouponType::Percent
            ? min(intdiv($subtotal * $coupon->value, 100), $coupon->max_discount ?? PHP_INT_MAX)
            : min($coupon->value, $subtotal);
    }

    private function paymentStatus(PaymentMethod $method, OrderStatus $status, ?Carbon $confirmedAt): PaymentStatus
    {
        if ($method === PaymentMethod::Cod) {
            return $status === OrderStatus::Delivered ? PaymentStatus::Paid : PaymentStatus::Unpaid;
        }

        // Chuyển khoản: xác nhận đơn sau khi nhận tiền; huỷ sau khi đã nhận tiền thì hoàn tiền
        return match (true) {
            $confirmedAt === null => PaymentStatus::Unpaid,
            $status === OrderStatus::Cancelled => PaymentStatus::Refunded,
            default => PaymentStatus::Paid,
        };
    }

    /**
     * Khách có tài khoản: lấy địa chỉ mặc định; khách vãng lai: sinh ngẫu nhiên.
     *
     * @return array<string, mixed>
     */
    private function recipient(?User $customer): array
    {
        if ($customer === null) {
            return ['receiver_name' => Vn::name(), 'phone' => Vn::phone(), 'email' => fake()->boolean(70) ? fake()->safeEmail() : null, ...Vn::address()];
        }

        $address = $customer->addresses->firstWhere('is_default', true);

        return [
            'receiver_name' => $address->receiver_name,
            'phone' => $address->phone,
            'email' => $customer->email,
            'province' => $address->province,
            'district' => $address->district,
            'ward' => $address->ward,
            'detail' => $address->detail,
        ];
    }
}
