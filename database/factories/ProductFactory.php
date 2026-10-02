<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producer_id' => Producer::factory(),
            'category_id' => Category::factory(),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'description' => fake()->sentence(10),
            'price' => fake()->numberBetween(100, 900),
            'unit' => 'шт',
            'in_stock' => true,
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['in_stock' => false]);
    }
}
