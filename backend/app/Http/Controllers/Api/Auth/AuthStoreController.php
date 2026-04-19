<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStoreRequest;
use App\Models\StoreOwner;
use Illuminate\Support\Facades\Hash;

class AuthStoreController extends Controller
{
    public function register(RegisterStoreRequest $request)
    {
        $validated = $request->validated();

        $user = StoreOwner::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'zip_code' => $validated['zip_code'],
            'address' => $validated['address'],
        ]);

        $logoPath = $request->hasFile('logo') 
            ? $request->file('logo')->store('stores/logos', 'public') 
            : null;
            
        $heroPath = $request->hasFile('hero_image') 
            ? $request->file('hero_image')->store('stores/heros', 'public') 
            : null;

        $user->store()->create([
            'store_name' => $validated['store_name'],
            'bio' => $validated['bio'] ?? null,
            'logo' => $logoPath,
            'hero_image' => $heroPath,
            'status' => 'pending_approval',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Store register success',
            'token' => $token,
            'user' => $user->load('store'),
        ], 201);
    }
}
