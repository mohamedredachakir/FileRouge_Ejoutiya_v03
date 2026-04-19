<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\UpsertMyStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Store::with('user')
            ->withCount('products')
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $stores = $query->latest()->paginate(12);

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

    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $query = Store::with([
            'user',
            'products' => function ($query) use ($user) {
                $query->with('images');

                if (! $user || $user->role !== 'store_owner') {
                    $query->where('status', 'active');
                }
            },
        ]);

        if (! $user || $user->role !== 'store_owner') {
            $query->where('status', 'active');
        } elseif ($user->role === 'store_owner') {
            $query->where(function ($storeQuery) use ($user) {
                $storeQuery->where('status', 'active')
                    ->orWhere('user_id', $user->id);
            });
        }

        $store = $query
            ->with([
                'user',
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
        $store = $request->user()->storeProfile()->first();

        $logoPath = $store ? $store->logo : null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('stores/logos', 'public');
        } elseif (is_string($request->logo)) {
            $logoPath = $request->logo;
        }

        $heroPath = $store ? $store->hero_image : null;
        if ($request->hasFile('hero_image')) {
            $heroPath = $request->file('hero_image')->store('stores/heros', 'public');
        } elseif (is_string($request->hero_image)) {
            $heroPath = $request->hero_image;
        }

        $store = $request->user()->storeProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'store_name' => $validated['store_name'],
                'bio' => $validated['bio'] ?? null,
                'logo' => $logoPath,
                'hero_image' => $heroPath,
                'status' => $store ? $store->status : 'pending_approval',
            ]
        );

        return response()->json([
            'message' => 'Store saved',
            'data' => $store,
        ], 201);
    }
}
