<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Admin always has all permissions
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Cache role permissions (cleared when role_permissions changes)
        $rolePermissions = Cache::remember('role_permissions:' . $user->role, 3600, function () use ($user) {
            return \DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role', $user->role)
                ->pluck('permissions.key')
                ->toArray();
        });

        foreach ($permissions as $permission) {
            if (in_array($permission, $rolePermissions)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
