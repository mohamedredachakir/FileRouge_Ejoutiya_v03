<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_register_with_extra_fields()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'city' => 'Casablanca',
            'zip_code' => '12345',
            'address' => '123 Main St',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id', 'name', 'email', 'phone', 'city', 'zip_code', 'address', 'role'
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'city' => 'Casablanca',
            'zip_code' => '12345',
            'address' => '123 Main St',
            'role' => 'client',
        ]);
    }

    public function test_registration_fails_without_extra_fields()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'city', 'zip_code', 'address']);
    }
}
