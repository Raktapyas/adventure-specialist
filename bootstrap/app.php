<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureTrailingSlash;
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
        // Render terminates TLS in front of the container; trusting its proxy
        // keeps secure cookies, redirect targets and generated URLs on https.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'canonical' => EnsureTrailingSlash::class,
            'admin' => EnsureIsAdmin::class,
        ]);

        // Authenticated visitors hitting guest-only pages (e.g. /login after
        // signing in) must not be bounced into the admin area where a
        // non-admin would hit a 403. Admins go to the dashboard; everyone
        // else returns to the public site.
        $middleware->redirectTo(
            users: fn (Request $request) => $request->user()?->is_admin
                ? route('dashboard')
                : route('home'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
