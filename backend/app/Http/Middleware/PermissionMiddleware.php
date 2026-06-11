<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();
        
        // Super Admin bypass
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $effectivePerms = app(\App\Services\PermissionService::class)->getEffectivePermissions($user);
        
        if (!$effectivePerms->contains('name', $permission)) {
            return response()->json(['message' => 'Forbidden. Missing permission: ' . $permission], 403);
        }

        return $next($request);
    }
}
