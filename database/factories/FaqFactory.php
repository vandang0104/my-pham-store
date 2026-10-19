<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group' => 'Đặt hàng',
            'question' => 'Tôi có thể đặt hàng mà không cần tài khoản không?',
            'answer' => 'Có. Bạn có thể đặt hàng với tư cách khách và tra cứu đơn bằng mã đơn và số điện thoại.',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
