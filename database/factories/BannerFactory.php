<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Ưu đãi mùa thu', 'Bộ sưu tập mới', 'Quà tặng tinh tế']),
            'image_desktop' => 'placeholders/banners/banner-desktop.svg',
            'image_mobile' => 'placeholders/banners/banner-mobile.svg',
            'link' => '/san-pham',
            'position' => 'home_hero',
            'sort_order' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
