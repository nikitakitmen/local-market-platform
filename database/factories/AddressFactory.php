<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use App\Models\User;
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
            'city_id' => City::factory(),
            'title' => 'Дом',
            'street' => fake()->streetAddress(),
            'apartment' => (string) fake()->numberBetween(1, 200),
            'distance_km' => 3,
            'is_default' => true,
        ];
    }
}
