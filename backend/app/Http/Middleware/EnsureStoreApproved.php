<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $store = $user->storeProfile;

        if (! $store) {
            return response()->json(['message' => 'Store profile not found.'], 404);
        }

        if ($store->status !== 'active') {
            return response()->json(['message' => 'Store is not approved yet.'], 403);
        }

        return $next($request);
    }
}
