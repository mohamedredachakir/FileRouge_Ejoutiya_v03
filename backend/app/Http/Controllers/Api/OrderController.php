<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        $validated = $request->validated();

        $userId = (int) $request->user()->id;
        $cart = Cart::with('items.product.store')->firstOrCreate([
            'client_id' => $userId,
        ]);

        $cartItems = $cart->items;

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $preparedItems = [];
        $checkoutStoreId = null;

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product) {
                return response()->json(['message' => 'Product not found in cart'], 422);
            }

            $store = $product->store;

            if (! $store || $store->status !== 'active') {
                return response()->json([
                    'message' => 'Product store is not active: ' . $product->name,
                ], 422);
            }

            if ($product->status !== 'active') {
                return response()->json([
                    'message' => 'Product is not available for checkout: ' . $product->name,
                ], 422);
            }

            if ($checkoutStoreId === null) {
                $checkoutStoreId = (int) $store->id;
            } elseif ($checkoutStoreId !== (int) $store->id) {
                return response()->json([
                    'message' => 'Checkout with products from multiple stores is not supported yet.',
                ], 422);
            }

            $quantity = (int) $item->quantity;

            if ($quantity <= 0) {
                return response()->json(['message' => 'Invalid quantity in cart'], 422);
            }

            if ((int) $product->stock < $quantity) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name,
                ], 422);
            }

            $preparedItems[] = [
                'product_id' => (int) $product->id,
                'quantity' => $quantity,
            ];
        }

        usort($preparedItems, fn (array $a, array $b) => $a['product_id'] <=> $b['product_id']);

        try {
            $order = DB::transaction(function () use ($request, $validated, $preparedItems) {
                $total = 0;

                $order = Order::create([
                    'client_id' => $request->user()->id,
                    'status' => 'pending',
                    'total_price' => 0,
                    'phone' => $validated['phone'],
                    'city' => $validated['city'],
                    'zip_code' => $validated['zip_code'],
                    'address' => $validated['address'],
                ]);

                $lockedCheckoutStoreId = null;

                foreach ($preparedItems as $prepared) {
                    /** @var Product|null $product */
                    $product = Product::with('store')
                        ->lockForUpdate()
                        ->find($prepared['product_id']);

                    if (! $product) {
                        throw new \RuntimeException('A product in your cart no longer exists.');
                    }

                    if ($product->status !== 'active') {
                        throw new \RuntimeException('Product is no longer available: ' . $product->name);
                    }

                    $store = $product->store;

                    if (! $store || $store->status !== 'active') {
                        throw new \RuntimeException('Store is no longer active for product: ' . $product->name);
                    }

                    if ($lockedCheckoutStoreId === null) {
                        $lockedCheckoutStoreId = (int) $store->id;
                    } elseif ($lockedCheckoutStoreId !== (int) $store->id) {
                        throw new \RuntimeException('Checkout with products from multiple stores is not supported yet.');
                    }

                    $quantity = (int) $prepared['quantity'];

                    if ((int) $product->stock < $quantity) {
                        throw new \RuntimeException('Not enough stock for product: ' . $product->name);
                    }

                    $priceSnapshot = (float) $product->price;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $priceSnapshot,
                    ]);

                    $product->decrement('stock', $quantity);
                    $total += $priceSnapshot * $quantity;
                }

                $order->update(['total_price' => $total]);

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $cart->items()->delete();

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order->load(['items.product']),
        ], 201);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with(['items.product'])
            ->where('client_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'My orders',
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function showMyOrder(Request $request, int $orderId)
    {
        $order = Order::with(['items.product'])
            ->where('client_id', $request->user()->id)
            ->find($orderId);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'message' => 'Order details',
            'data' => $order,
        ]);
    }

    public function storeOrders(Request $request)
    {
        $store = $request->user()->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found'], 404);
        }

        $orders = Order::with([
            'client',
            'items' => function ($query) use ($store) {
                $query->whereHas('product', function ($productQuery) use ($store) {
                    $productQuery->where('store_id', $store->id);
                })->with('product');
            },
        ])
            ->whereHas('items.product', function ($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Store orders',
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function updateStoreOrderStatus(UpdateOrderStatusRequest $request, int $orderId)
    {
        $validated = $request->validated();

        $store = $request->user()->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found'], 404);
        }

        $order = Order::with('items.product')->whereHas('items.product', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->find($orderId);

        if (! $order) {
            return response()->json(['message' => 'Order not found for your store'], 404);
        }

        $storeIdsInOrder = $order->items
            ->pluck('product.store_id')
            ->filter()
            ->unique()
            ->values();

        if ($storeIdsInOrder->count() > 1) {
            return response()->json([
                'message' => 'Cannot update status for mixed-store orders.',
            ], 422);
        }

        if ($storeIdsInOrder->isEmpty() || (int) $storeIdsInOrder->first() !== (int) $store->id) {
            return response()->json(['message' => 'Order not found for your store'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be updated'], 422);
        }

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order->fresh('items.product'),
        ]);
    }
}
