<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Database\Factories\Support\Vn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'receiver_name' => Vn::name(),
            'phone' => Vn::phone(),
            ...Vn::address(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
