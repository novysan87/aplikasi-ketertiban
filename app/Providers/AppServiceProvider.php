<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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

            return $user->canPermission($permission);
        });

        // Blade directive: @canAnyPermission('a', 'b', ...) — true jika user punya SALAH SATU permission
        Blade::if('canAnyPermission', function (string ...$permissions) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }
            foreach ($permissions as $permission) {
                if ($user->canPermission($permission)) {
                    return true;
                }
            }
            return false;
        });
    }
}
