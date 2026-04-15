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
        $cart = Cart::with('items.product')->firstOrCreate([
            'client_id' => $userId,
        ]);

        $cartItems = $cart->items;

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $preparedItems = [];

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product) {
                return response()->json(['message' => 'Product not found in cart'], 422);
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
                'product' => $product,
                'quantity' => $quantity,
                // Snapshot from current DB price for safer checkout.
                'price' => (float) $product->price,
            ];
        }

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

            foreach ($preparedItems as $prepared) {
                /** @var Product $product */
                $product = $prepared['product'];
                $quantity = $prepared['quantity'];
                $priceSnapshot = $prepared['price'];

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
            ->get();

        return response()->json([
            'message' => 'My orders',
            'data' => $orders,
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
            ->get();

        return response()->json([
            'message' => 'Store orders',
            'data' => $orders,
        ]);
    }

    public function updateStoreOrderStatus(UpdateOrderStatusRequest $request, int $orderId)
    {
        $validated = $request->validated();

        $store = $request->user()->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found'], 404);
        }

        $order = Order::whereHas('items.product', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->find($orderId);

        if (! $order) {
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
