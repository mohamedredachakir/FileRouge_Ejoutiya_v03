<?php

namespace Database\Factories;

use App\Models\StoreOwner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreOwnerFactory extends Factory
{
    protected $model = StoreOwner::class;

    public function configure(): static
    {
        return $this->afterMaking(function (StoreOwner $storeOwner) {
            if ($storeOwner->role === null) {
                $storeOwner->role = 'store_owner';
            }
        })->afterCreating(function (StoreOwner $storeOwner) {
            if ($storeOwner->role !== 'store_owner') {
                $storeOwner->update(['role' => 'store_owner']);
            }
        });
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'store_owner',
            'is_banned' => false,
            'remember_token' => Str::random(10),
        ];
    }
}
