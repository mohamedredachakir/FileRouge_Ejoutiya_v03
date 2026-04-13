<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'message' => 'Profile fetched',
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated',
            'user' => $user->fresh(),
        ]);
    }
}
