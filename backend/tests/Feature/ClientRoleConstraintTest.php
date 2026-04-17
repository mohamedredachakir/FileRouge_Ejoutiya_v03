<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Cart;
use App\Models\Client;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRoleConstraintTest extends TestCase
{
    use RefreshDatabase;

    private $storeOwner;

    private $admin;

    private $client;

    private $store;

    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeOwner = StoreOwner::factory()->create();
        $this->admin = Admin::factory()->create();
        $this->client = Client::factory()->create();

        $this->store = Store::factory()->create(['user_id' => $this->storeOwner->id, 'status' => 'active']);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'active',
            'stock' => 10,
        ]);
    }

    /**
     * Cart endpoints - only clients can access
     */
    public function test_cart_show_requires_client_role()
    {
        $response = $this->actingAs($this->storeOwner)
            ->getJson('/api/cart');

        $response->assertStatus(403);
    }

    public function test_cart_add_item_requires_client_role()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(403);
    }

    public function test_cart_update_item_requires_client_role()
    {
        $response = $this->actingAs($this->storeOwner)
            ->putJson('/api/cart/items/' . $this->product->id, [
                'quantity' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_cart_remove_item_requires_client_role()
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/cart/items/' . $this->product->id);

        $response->assertStatus(403);
    }

    public function test_cart_clear_requires_client_role()
    {
        $response = $this->actingAs($this->storeOwner)
            ->deleteJson('/api/cart');

        $response->assertStatus(403);
    }

    /**
     * Checkout endpoint - only clients can access
     */
    public function test_checkout_requires_client_role()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(403);
    }

    public function test_store_owner_cannot_checkout()
    {
        $response = $this->actingAs($this->storeOwner)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Client order endpoints - only clients can access
     */
    public function test_my_orders_requires_client_role()
    {
        $response = $this->actingAs($this->storeOwner)
            ->getJson('/api/orders/me');

        $response->assertStatus(403);
    }

    public function test_show_my_order_requires_client_role()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/orders/me/1');

        $response->assertStatus(403);
    }

    /**
     * Positive tests - client can access their endpoints
     */
    public function test_client_can_access_cart()
    {
        $response = $this->actingAs($this->client)
            ->getJson('/api/cart');

        $response->assertStatus(200);
    }

    public function test_client_can_add_to_cart()
    {
        $response = $this->actingAs($this->client)
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(201);
    }

    public function test_client_can_checkout()
    {
        // Add item to cart first
        $this->actingAs($this->client)
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 1,
            ]);

        $response = $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(201);
    }

    public function test_client_can_view_own_orders()
    {
        $response = $this->actingAs($this->client)
            ->getJson('/api/orders/me');

        $response->assertStatus(200);
    }
}
