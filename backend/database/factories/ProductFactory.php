<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'name' => fake()->word(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(1, 100),
            'category' => fake()->randomElement(['t_shirt', 'hoodie', 'pants', 'sneakers', 'accessories']),
            'status' => 'active',
        ];
    }
}
