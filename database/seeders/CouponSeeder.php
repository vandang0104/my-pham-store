<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/**
 * 6 coupon đủ tình huống. used_count được OrderSeeder đếm lại theo đơn thực tế.
 */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Đang hiệu lực, giảm % có trần
        Coupon::factory()->percent(10, maxDiscount: 50000)->create([
            'code' => 'DEPXINH10', 'min_order' => 300000, 'usage_limit' => 200,
            'starts_at' => now()->subDays(90), 'expires_at' => now()->addDays(60),
        ]);

        // Giảm cố định, đang hiệu lực
        Coupon::factory()->create([
            'code' => 'GIAM50K', 'value' => 50000, 'min_order' => 500000, 'usage_limit' => 100,
            'starts_at' => now()->subDays(30), 'expires_at' => now()->addDays(30),
        ]);

        // Giảm % có trần cao, cho đơn lớn
        Coupon::factory()->percent(25, maxDiscount: 150000)->create([
            'code' => 'VIP25', 'min_order' => 800000, 'usage_limit' => 50,
            'starts_at' => now()->subDays(60), 'expires_at' => now()->addDays(90),
        ]);

        // Hết hạn
        Coupon::factory()->percent(15, maxDiscount: 100000)->create([
            'code' => 'TET2026', 'min_order' => 200000,
            'starts_at' => now()->subDays(280), 'expires_at' => now()->subDays(230),
        ]);

        // Hết lượt: còn trong thời gian hiệu lực nhưng đã dùng đủ usage_limit (2 đơn trong OrderSeeder)
        Coupon::factory()->percent(20, maxDiscount: 80000)->create([
            'code' => 'FLASH20', 'min_order' => 0, 'usage_limit' => 2,
            'starts_at' => now()->subDays(45), 'expires_at' => now()->addDays(15),
        ]);

        // Chưa bắt đầu
        Coupon::factory()->upcoming()->create([
            'code' => 'HEVUI', 'value' => 40000, 'min_order' => 350000, 'usage_limit' => 300,
        ]);
    }
}
