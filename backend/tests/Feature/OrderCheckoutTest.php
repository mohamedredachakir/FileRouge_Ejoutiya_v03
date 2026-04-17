<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private $client;

    private $storeOwner;

    private $store;

    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeOwner = StoreOwner::factory()->create();
        $this->store = Store::factory()->create(['user_id' => $this->storeOwner->id, 'status' => 'active']);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'active',
            'stock' => 10,
        ]);
        $this->client = Client::factory()->create();
    }

    public function test_checkout_with_empty_cart_returns_422()
    {
        $response = $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Cart is empty');
    }

    public function test_checkout_creates_order_with_at_least_one_item()
    {
        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['client_id' => $this->client->id]);

        $order = Order::where('client_id', $this->client->id)->first();
        $this->assertNotNull($order);
        $this->assertGreaterThanOrEqual(1, $order->items->count());
    }

    public function test_checkout_with_inactive_store_returns_422()
    {
        $this->store->update(['status' => 'suspended']);

        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
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

        $response->assertStatus(422);
    }

    public function test_checkout_with_inactive_product_returns_422()
    {
        $this->product->update(['status' => 'hidden']);

        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
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

        $response->assertStatus(422);
    }

    public function test_checkout_with_insufficient_stock_returns_422()
    {
        $this->product->update(['stock' => 1]);

        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Not enough stock for product: ' . $this->product->name);
    }

    public function test_checkout_with_multiple_stores_returns_422()
    {
        $storeOwner2 = StoreOwner::factory()->create();
        $store2 = Store::factory()->create(['user_id' => $storeOwner2->id, 'status' => 'active']);
        $product2 = Product::factory()->create([
            'store_id' => $store2->id,
            'status' => 'active',
            'stock' => 10,
        ]);

        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Checkout with products from multiple stores is not supported yet.');
    }

    public function test_checkout_clears_cart_after_success()
    {
        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_checkout_decrements_product_stock()
    {
        $initialStock = $this->product->stock;

        $cart = Cart::create(['client_id' => $this->client->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($this->client)
            ->postJson('/api/orders/checkout', [
                'phone' => '1234567890',
                'city' => 'Test City',
                'zip_code' => '12345',
                'address' => 'Test Address',
            ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->stock);
    }
}
