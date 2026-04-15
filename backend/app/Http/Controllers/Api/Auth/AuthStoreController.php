<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthStoreController extends Controller
{
    public function register(RegisterStoreRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'store_owner',
        ]);

        $user->storeProfile()->create([
            'store_name' => $validated['store_name'],
            'bio' => $validated['bio'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'hero_image' => $validated['hero_image'] ?? null,
            'status' => 'pending_approval',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Store register success',
            'token' => $token,
            'user' => $user->load('storeProfile'),
        ], 201);
    }
}
