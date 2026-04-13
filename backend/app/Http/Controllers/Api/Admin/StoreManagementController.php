<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;

class StoreManagementController extends Controller
{
    public function index()
    {
        $stores = Store::with('user')->latest()->get();

        return response()->json([
            'message' => 'Stores list',
            'data' => $stores,
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
        $store = Store::find($id);

        if (! $store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->update(['status' => $status]);

        return response()->json([
            'message' => $message,
            'data' => $store,
        ]);
    }
}
