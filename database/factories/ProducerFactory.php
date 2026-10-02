<?php

namespace Database\Factories;

use App\Enums\ProducerStatus;
use App\Enums\ProducerType;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producer>
 */
class ProducerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->role(UserRole::Producer),
            'city_id' => City::factory(),
            'name' => 'Ферма '.fake()->unique()->lastName(),
            'type' => ProducerType::Individual,
            'description' => fake()->sentence(12),
            'phone' => '+7 (900) 123-45-67',
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress(),
            'status' => ProducerStatus::Approved,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ProducerStatus::Pending, 'approved_at' => null]);
    }
}
