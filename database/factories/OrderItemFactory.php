<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Snapshot tên/sku/giá lấy từ sản phẩm tại thời điểm tạo.
 *
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes) => Product::withTrashed()->find($attributes['product_id'])->name,
            'sku' => fn (array $attributes) => Product::withTrashed()->find($attributes['product_id'])->sku,
            'price' => fn (array $attributes) => Product::withTrashed()->find($attributes['product_id'])->current_price,
            'quantity' => fake()->numberBetween(1, 3),
            'line_total' => fn (array $attributes) => $attributes['price'] * $attributes['quantity'],
        ];
    }
}
