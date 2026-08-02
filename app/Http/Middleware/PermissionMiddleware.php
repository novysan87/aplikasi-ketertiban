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
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Gabungkan permission dari SEMUA role user (multi-role)
        $userPermissions = $user->allPermissions();

        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
