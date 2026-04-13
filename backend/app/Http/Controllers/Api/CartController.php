<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CartController extends Controller
{
    private const TTL_SECONDS = 86400;

    public function show(Request $request)
    {
        return response()->json([
            'message' => 'Cart data',
            'data' => $this->getCartItems($request->user()->id),
        ]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $items = $this->getCartItems($request->user()->id);
        $existingIndex = $this->findItemIndex($items, $product->id);

        if ($existingIndex !== null) {
            $items[$existingIndex]['quantity'] += $validated['quantity'];
        } else {
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $validated['quantity'],
                'price' => (float) $product->price,
            ];
        }

        $this->saveCartItems($request->user()->id, $items);

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $items,
        ], 201);
    }

    public function updateItem(Request $request, int $productId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = $this->getCartItems($request->user()->id);
        $index = $this->findItemIndex($items, $productId);

        if ($index === null) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $items[$index]['quantity'] = $validated['quantity'];
        $this->saveCartItems($request->user()->id, $items);

        return response()->json([
            'message' => 'Cart item updated',
            'data' => $items,
        ]);
    }

    public function removeItem(Request $request, int $productId)
    {
        $items = $this->getCartItems($request->user()->id);
        $items = array_values(array_filter($items, fn ($item) => (int) $item['product_id'] !== $productId));

        $this->saveCartItems($request->user()->id, $items);

        return response()->json([
            'message' => 'Cart item removed',
            'data' => $items,
        ]);
    }

    public function clear(Request $request)
    {
        Redis::del($this->cartKey($request->user()->id));

        return response()->json(['message' => 'Cart cleared']);
    }

    private function cartKey(int $userId): string
    {
        return 'cart:' . $userId;
    }

    private function getCartItems(int $userId): array
    {
        $raw = Redis::get($this->cartKey($userId));

        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveCartItems(int $userId, array $items): void
    {
        Redis::setex($this->cartKey($userId), self::TTL_SECONDS, json_encode(array_values($items)));
    }

    private function findItemIndex(array $items, int $productId): ?int
    {
        foreach ($items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) === $productId) {
                return $index;
            }
        }

        return null;
    }
}
