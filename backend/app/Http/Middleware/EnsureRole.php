<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowedRoles = array_map('strtolower', $roles);
        $userRole = strtolower((string) $user->role);

        if (! in_array($userRole, $allowedRoles, true)) {
            return response()->json(['message' => 'Forbidden: invalid role.'], 403);
        }

        return $next($request);
    }
}
