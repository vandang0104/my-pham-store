<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Database\Factories\Support\Vn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Đơn hàng có tổng tiền nhất quán: total = subtotal - discount + shipping_fee.
 * Chưa có chi tiết đơn — tạo kèm OrderItem khi cần.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2, 30) * 50000;
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;

        return [
            'code' => 'DH'.now()->format('ymd').Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'coupon_id' => null,
            'receiver_name' => Vn::name(),
            'phone' => Vn::phone(),
            'email' => fake()->safeEmail(),
            ...Vn::address(),
            'payment_method' => PaymentMethod::Cod,
            'payment_status' => PaymentStatus::Unpaid,
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'discount' => 0,
            'shipping_fee' => $shippingFee,
            'total' => $subtotal + $shippingFee,
            'note' => null,
        ];
    }

    public function guest(): static
    {
        return $this->state(['user_id' => null]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'confirmed_at' => in_array($status, [OrderStatus::Confirmed, OrderStatus::Shipping, OrderStatus::Delivered], true) ? now()->subDays(4) : null,
            'shipped_at' => in_array($status, [OrderStatus::Shipping, OrderStatus::Delivered], true) ? now()->subDays(3) : null,
            'delivered_at' => $status === OrderStatus::Delivered ? now()->subDay() : null,
            'cancelled_at' => $status === OrderStatus::Cancelled ? now()->subDays(2) : null,
            'payment_status' => $status === OrderStatus::Delivered ? PaymentStatus::Paid : $attributes['payment_status'],
        ]);
    }

    public function delivered(): static
    {
        return $this->status(OrderStatus::Delivered);
    }
}
