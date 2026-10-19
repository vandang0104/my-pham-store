<?php

namespace Database\Factories;

use App\Enums\SkinType;
use App\Enums\UserRole;
use App\Models\User;
use Database\Factories\Support\Vn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Vn::name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => Vn::phone(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Customer,
            'birthday' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'skin_type' => fake()->randomElement(SkinType::cases()),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::Admin, 'skin_type' => null, 'birthday' => null]);
    }

    public function staff(): static
    {
        return $this->state(['role' => UserRole::Staff, 'skin_type' => null, 'birthday' => null]);
    }

    public function customer(): static
    {
        return $this->state(['role' => UserRole::Customer]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
