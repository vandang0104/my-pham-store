<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /** Mặc định: giảm cố định, đang hiệu lực, không giới hạn lượt */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'type' => CouponType::Fixed,
            'value' => 50000,
            'min_order' => 0,
            'max_discount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => now()->subDays(7),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }

    public function percent(int $value = 10, int $maxDiscount = 50000): static
    {
        return $this->state(['type' => CouponType::Percent, 'value' => $value, 'max_discount' => $maxDiscount]);
    }

    public function expired(): static
    {
        return $this->state(['starts_at' => now()->subDays(60), 'expires_at' => now()->subDay()]);
    }

    public function upcoming(): static
    {
        return $this->state(['starts_at' => now()->addDays(7), 'expires_at' => now()->addDays(37)]);
    }

    public function exhausted(int $limit = 10): static
    {
        return $this->state(['usage_limit' => $limit, 'used_count' => $limit]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
