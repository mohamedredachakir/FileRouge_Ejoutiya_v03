<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);

        return response()->json([
            'message' => 'Users list',
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function ban(int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update(['is_banned' => true]);

        return response()->json([
            'message' => 'User banned',
            'data' => $user,
        ]);
    }

    public function unban(int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update(['is_banned' => false]);

        return response()->json([
            'message' => 'User unbanned',
            'data' => $user,
        ]);
    }
}
