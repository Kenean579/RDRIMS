<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\University;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     * Resolve the tenant (university) from the authenticated user and
     * inject it into the request for downstream use.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        // Super Admin bypasses tenant validation entirely
        if ($user->hasRole('super_admin')) {
            $request->attributes->set('tenant_id', null);
            return $next($request);
        }

        $tenantId = $user->university_id;
        if (! $tenantId) {
            return response()->json(['message' => 'Tenant not assigned'], 403);
        }
        // Resolve University model for possible later use
        $tenant = University::findOrFail($tenantId);
        // Bind to container and request attributes
        app()->instance('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenantId);
        return $next($request);
    }
}
