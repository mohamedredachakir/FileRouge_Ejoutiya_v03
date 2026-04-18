<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\UpsertMyStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::with('user')
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        return response()->json([
            'message' => 'Stores list',
            'data' => $stores->items(),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $store = Store::where('status', 'active')
            ->with([
                'user',
                'products' => function ($query) {
                    $query->where('status', 'active')->with('images');
                },
            ])
            ->find($id);

        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        return response()->json([
            'message' => 'Store details',
            'data' => $store,
        ]);
    }

    public function myStore(Request $request)
    {
        $store = $request->user()->storeProfile()->with('products.images')->first();

        if (! $store) {
            return response()->json([
                'message' => 'No store found for current user',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'My store',
            'data' => $store,
        ]);
    }

    public function upsertMyStore(UpsertMyStoreRequest $request)
    {
        if ($request->user()?->role !== 'store_owner') {
            return response()->json(['message' => 'Only store owners can manage a store.'], 403);
        }

        $validated = $request->validated();

        $store = $request->user()->storeProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'store_name' => $validated['store_name'],
                'bio' => $validated['bio'] ?? null,
                'logo' => $validated['logo'] ?? null,
                'hero_image' => $validated['hero_image'] ?? null,
                'status' => 'pending_approval',
            ]
        );

        return response()->json([
            'message' => 'Store saved',
            'data' => $store,
        ], 201);
    }
}
