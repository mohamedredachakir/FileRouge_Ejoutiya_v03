<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'user_id' => StoreOwner::factory(),
            'store_name' => fake()->company(),
            'bio' => fake()->paragraph(),
            'status' => 'active',
        ];
    }
}
