<?php

use App\Models\Visitor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\ModuleMiddleware;
use App\Http\Middleware\ShareGroupModules;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\VisitorTrackingMiddleware;
use App\Http\Middleware\RedirectIfNotAuthenticated;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('admin/')
                ->middleware(['web', 'share-modules'])
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectTo('admin/login', 'admin');
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->alias([
            'share-modules' => ShareGroupModules::class,
            'permission' => PermissionMiddleware::class,
            'track-visitor' => VisitorTrackingMiddleware::class,
            'mobile.active' => \App\Http\Middleware\EnsureMobileUserActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
