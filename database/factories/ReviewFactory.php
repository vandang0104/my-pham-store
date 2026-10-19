<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->randomElement([
                'Sản phẩm dùng rất thích, thấm nhanh và không bết dính.',
                'Mùi dễ chịu, đóng gói cẩn thận, giao hàng nhanh.',
                'Dùng ổn, sẽ theo dõi thêm một thời gian nữa.',
            ]),
            'images' => null,
            'status' => ReviewStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => ReviewStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => ReviewStatus::Rejected]);
    }
}
