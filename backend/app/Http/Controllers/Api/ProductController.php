<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['store', 'images'])
            ->latest();

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $products = $query->get();

        return response()->json([
            'message' => 'Products list',
            'data' => $products,
        ]);
    }

    public function show(int $id)
    {
        $product = Product::with(['store', 'images'])->find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'message' => 'Product details',
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string'],
            'status' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:255'],
        ]);

        $store = $request->user()->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found'], 404);
        }

        $product = $store->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category' => $validated['category'],
            'status' => $validated['status'],
        ]);

        foreach (($validated['images'] ?? []) as $index => $path) {
            $product->images()->create([
                'image_path' => $path,
                'sort_order' => $index,
            ]);
        }

        return response()->json([
            'message' => 'Product created',
            'data' => $product->fresh()->load(['store', 'images']),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $store = $request->user()->storeProfile;

        if (! $store || (int) $product->store_id !== (int) $store->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['string', 'max:255'],
        ]);

        $product->update(collect($validated)->except('images')->toArray());

        if (array_key_exists('images', $validated)) {
            $product->images()->delete();

            foreach (($validated['images'] ?? []) as $index => $path) {
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated',
            'data' => $product->fresh()->load(['store', 'images']),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $store = $request->user()->storeProfile;

        if (! $store || (int) $product->store_id !== (int) $store->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
