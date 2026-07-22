<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');

        // Blade directive: @canPermission('sync-data')
        Blade::if('canPermission', function (string $permission) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }
            if ($user->role === 'admin') {
                return true;
            }
            $rolePermissions = Cache::remember('role_permissions:' . $user->role, 3600, function () use ($user) {
                return \DB::table('role_permissions')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->where('role_permissions.role', $user->role)
                    ->pluck('permissions.key')
                    ->toArray();
            });
            return in_array($permission, $rolePermissions);
        });
    }
}
