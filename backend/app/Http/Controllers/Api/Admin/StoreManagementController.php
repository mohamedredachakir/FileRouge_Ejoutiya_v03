<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;

class StoreManagementController extends Controller
{
    private function formatStore(Store $store): array
    {
        return [
            'id' => $store->id,
            'store_name' => $store->store_name,
            'bio' => $store->bio,
            'logo_url' => $store->logo_url,
            'hero_image_url' => $store->hero_image_url,
            'status' => $store->status,
            'products_count' => (int) ($store->products_count ?? 0),
            'owner' => $store->user,
            'created_at' => $store->created_at,
            'updated_at' => $store->updated_at,
        ];
    }

    public function index()
    {
        $stores = Store::with('user')->withCount('products')->latest()->paginate(15);

        return response()->json([
            'message' => 'Stores list',
            'data' => collect($stores->items())
                ->map(fn (Store $store) => $this->formatStore($store))
                ->values(),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ],
        ]);
    }

    public function approve(int $id)
    {
        return $this->updateStatus($id, 'active', 'Store approved');
    }

    public function suspend(int $id)
    {
        return $this->updateStatus($id, 'suspended', 'Store suspended');
    }

    public function reject(int $id)
    {
        return $this->updateStatus($id, 'pending_approval', 'Store rejected to pending state');
    }

    private function updateStatus(int $id, string $status, string $message)
    {
        $store = Store::with('user')->withCount('products')->find($id);

        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->update(['status' => $status]);

        return response()->json([
            'message' => $message,
            'data' => $this->formatStore($store),
        ]);
    }
}
