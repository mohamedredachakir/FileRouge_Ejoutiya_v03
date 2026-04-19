<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        return response()->json([
            'message' => 'Cart data',
            'data' => $cart->load('items.product'),
        ]);
    }

    public function addItem(AddCartItemRequest $request)
    {
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);
        $cart = $this->getOrCreateCart($request);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('size', $validated['size'])
            ->first();

        if ($item) {
            $item->update(['quantity' => $validated['quantity']]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'size' => $validated['size'],
            ]);
        }

        $cart->load('items.product');

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $cart,
        ], 201);
    }

    public function updateItem(UpdateCartItemRequest $request, int $productId)
    {
        $validated = $request->validated();
        $size = $request->input('size'); // Optional if we want to target specific size or just single entry

        $cart = $this->getOrCreateCart($request);

        $query = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId);
            
        if ($size) {
            $query->where('size', $size);
        }

        $item = $query->first();

        if (! $item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $item->update(['quantity' => $validated['quantity']]);
        $cart->load('items.product');

        return response()->json([
            'message' => 'Cart item updated',
            'data' => $cart,
        ]);
    }

    public function removeItem(Request $request, int $productId)
    {
        $size = $request->input('size'); 
        $cart = $this->getOrCreateCart($request);

        $query = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId);
            
        if ($size) {
            $query->where('size', $size);
        }

        $query->delete();

        $cart->load('items.product');

        return response()->json([
            'message' => 'Cart item removed',
            'data' => $cart,
        ]);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    private function getOrCreateCart(Request $request): Cart
    {
        return Cart::firstOrCreate([
            'client_id' => $request->user()->id,
        ]);
    }
}
