<?php

use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\PreventAdminResponseCaching;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'no-admin-cache' => PreventAdminResponseCaching::class,
        ]);

        // Admin routes redirect to the admin login, not the storefront's
        // sign-in page; everything else keeps the existing behavior.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.login')
            : route('sign-in'));

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin*')) {
                return $request->user()?->role === 'superadmin'
                    ? route('admin.dashboard')
                    : route('user-dashboard');
            }

            return route('user-dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
