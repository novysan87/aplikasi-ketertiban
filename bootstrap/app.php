<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SingleSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Halaman error ramah user (kecuali permintaan JSON)
        $htmlView = function (string $view, int $code, Request $request): ?\Illuminate\Http\Response {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }
            return response()->view($view, [], $code);
        };

        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) use ($htmlView) {
            return $htmlView('errors.404', 404, $request);
        });
        $exceptions->renderable(function (AccessDeniedHttpException $e, Request $request) use ($htmlView) {
            return $htmlView('errors.403', 403, $request);
        });
        $exceptions->renderable(function (HttpException $e, Request $request) use ($htmlView) {
            $view = match ($e->getStatusCode()) {
                419 => 'errors.419',
                403 => 'errors.403',
                503 => 'errors.503',
                default => null,
            };
            if (!$view) {
                return null;
            }
            return $htmlView($view, $e->getStatusCode(), $request);
        });
        $exceptions->renderable(function (\Throwable $e, Request $request) use ($htmlView) {
            // Biarkan default menangani (redirect ke login)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }
            // Biarkan default menangani: redirect balik + pesan error validasi
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }
            \Illuminate\Support\Facades\Log::error('Unhandled exception: ' . get_class($e) . ' - ' . $e->getMessage() . ' @ ' . $request->path());
            return $htmlView('errors.500', 500, $request);
        });
    })->create();
