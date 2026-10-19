<?php

namespace Database\Factories;

use App\Enums\SkinType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Sữa rửa mặt', 'Serum', 'Kem dưỡng', 'Toner', 'Mặt nạ', 'Kem chống nắng'])
            .' '.fake()->randomElement(['Rau Má', 'Trà Xanh', 'Niacinamide', 'Vitamin C', 'Ceramide', 'Hoa Cúc', 'Nha Đam'])
            .' '.fake()->randomElement(['Dịu Nhẹ', 'Cấp Ẩm', 'Sáng Da', 'Phục Hồi', 'Kiềm Dầu']);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sku' => 'SP-'.Str::upper(Str::random(8)),
            'short_description' => "{$name} phù hợp dùng hằng ngày.",
            'description' => "{$name} giúp chăm sóc da nhẹ nhàng.\n\n## Công dụng\n\n- Làm dịu da\n- Cấp ẩm",
            'ingredients' => 'Nước tinh khiết, Glycerin, Panthenol.',
            'usage_guide' => "1. Làm sạch da.\n2. Thoa một lượng vừa đủ.",
            // Giá VND, bội số 1.000
            'price' => fake()->numberBetween(99, 990) * 1000,
            'sale_price' => null,
            'stock' => fake()->numberBetween(10, 150),
            'volume' => fake()->randomElement(['30ml', '50ml', '100ml', '150ml', '200g']),
            'skin_type' => fake()->randomElement(SkinType::cases()),
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    /** Giảm 10–30%, vẫn là bội số 1.000 và nhỏ hơn giá gốc */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => (int) (floor($attributes['price'] * fake()->numberBetween(70, 90) / 100 / 1000) * 1000),
        ]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
