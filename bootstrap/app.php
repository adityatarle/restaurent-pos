<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfig;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (MiddlewareConfig $middleware) {
        $middleware->alias([
            // Correct path for the default Authenticate middleware
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class, // <<<<------ CORRECTED THIS LINE

            // Other standard aliases (ensure their paths are correct too if you list them)
            // 'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            // 'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class, // This one is typically in app/Http/Middleware
            'password.confirm' => \Illuminate\Auth\Middleware\EnsurePasswordIsConfirmed::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // YOUR CUSTOM MIDDLEWARE ALIASES:
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'reception'  => \App\Http\Middleware\ReceptionMiddleware::class,
            'waiter'     => \App\Http\Middleware\WaiterMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();