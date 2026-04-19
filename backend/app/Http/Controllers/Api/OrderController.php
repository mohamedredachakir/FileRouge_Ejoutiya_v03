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
    private function formatStoreOrder(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => $item->product,
                'size' => $item->size,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'unit_price' => (float) $item->price,
            ];
        })->values();

        return [
            'id' => $order->id,
            'reference' => 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'status' => $order->status,
            'total' => (float) $order->total_price,
            'total_price' => (float) $order->total_price,
            'phone' => $order->phone,
            'city' => $order->city,
            'zip_code' => $order->zip_code,
            'address' => $order->address,
            'items' => $items,
            'client' => $order->client,
            'created_at' => $order->created_at,
        ];
    }

    public function checkout(CheckoutRequest $request)
    {
        $validated = $request->validated();

        $userId = (int) $request->user()->id;
        $cart = Cart::with('items.product.store')->firstOrCreate([
            'client_id' => $userId,
        ]);

        $cartItems = collect($validated['items'] ?? [])
            ->map(function (array $item) {
                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    'size' => (string) $item['size'],
                ];
            });

        if ($cartItems->isEmpty()) {
            $cartItems = $cart->items->map(function ($item) {
                return [
                    'product_id' => (int) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'size' => (string) $item->size,
                ];
            });
        }

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $validated, $cartItems) {
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

                foreach ($cartItems as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product) {
                        throw new \RuntimeException('Product not found: ' . $item['product_id']);
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \RuntimeException('Not enough stock for: ' . $product->name);
                    }

                    $priceSnapshot = (float) $product->price;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $priceSnapshot,
                        'size' => $item['size'],
                    ]);

                    $product->decrement('stock', $item['quantity']);
                    $total += $priceSnapshot * $item['quantity'];
                }

                $order->update(['total_price' => $total]);

                // Update user delivery info for next time
                $request->user()->update([
                    'phone' => $validated['phone'],
                    'city' => $validated['city'],
                    'zip_code' => $validated['zip_code'],
                    'address' => $validated['address'],
                ]);

                return $order;
            });
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $cart->items()->delete();

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order->load(['items.product']),
            'user' => $request->user(),
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
            'data' => collect($orders->items())->map(fn (Order $order) => $this->formatStoreOrder($order))->values(),
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

        $nextStatus = $validated['status'];

        $allowedTransitions = [
            'pending' => ['confirmed', 'rejected'],
            'confirmed' => ['delivery', 'rejected'],
        ];

        if (!isset($allowedTransitions[$order->status]) || !in_array($nextStatus, $allowedTransitions[$order->status], true)) {
            return response()->json([
                'message' => 'Invalid status transition from ' . $order->status . ' to ' . $nextStatus,
            ], 422);
        }

        $order->update(['status' => $nextStatus]);

        return response()->json([
            'message' => 'Order status updated',
            'data' => $this->formatStoreOrder($order->fresh(['items.product', 'client'])),
        ]);
    }
}
