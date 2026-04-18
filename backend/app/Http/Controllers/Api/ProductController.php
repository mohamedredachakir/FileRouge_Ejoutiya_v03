<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['store', 'images'])
            ->where('status', 'active')
            ->whereHas('store', function ($storeQuery) {
                $storeQuery->where('status', 'active');
            })
            ->latest();

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        $products = $query->paginate(12);

        return response()->json([
            'message' => 'Products list',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $product = Product::with(['store', 'images'])
            ->where('status', 'active')
            ->whereHas('store', function ($storeQuery) {
                $storeQuery->where('status', 'active');
            })
            ->find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'message' => 'Product details',
            'data' => $product,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateProductRequest $request, int $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $store = $request->user()->storeProfile;

        if (! $store || (int) $product->store_id !== (int) $store->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

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

    public function myStoreProducts(Request $request)
    {
        $store = $request->user()->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found'], 404);
        }

        $products = $store->products()->with('images')->latest()->get();

        return response()->json([
            'message' => 'Store products list',
            'data' => $products,
        ]);
    }
}
