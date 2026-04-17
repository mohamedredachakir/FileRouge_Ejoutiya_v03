<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Client $client) {
            if ($client->role === null) {
                $client->role = 'client';
            }
        })->afterCreating(function (Client $client) {
            if ($client->role !== 'client') {
                $client->update(['role' => 'client']);
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
            'role' => 'client',
            'is_banned' => false,
            'remember_token' => Str::random(10),
        ];
    }
}
