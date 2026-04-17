<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Admin $admin) {
            if ($admin->role === null) {
                $admin->role = 'admin';
            }
        })->afterCreating(function (Admin $admin) {
            if ($admin->role !== 'admin') {
                $admin->update(['role' => 'admin']);
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
            'role' => 'admin',
            'is_banned' => false,
            'remember_token' => Str::random(10),
        ];
    }
}
