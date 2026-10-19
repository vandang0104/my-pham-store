<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        // Tên hư cấu
        $name = fake()->randomElement(['Lam Viên', 'Mộc Sương', 'Hạt Sương', 'Sen Hồng', 'Gió Biển', 'Nhã Thanh']).' '
            .fake()->randomElement(['Lab', 'Beauty', 'Atelier', 'Spa']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'description' => 'Thương hiệu mỹ phẩm hư cấu dùng cho dữ liệu mẫu.',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
