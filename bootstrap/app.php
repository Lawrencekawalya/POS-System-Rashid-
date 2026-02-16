<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\ViewServiceProvider;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        ViewServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR |
                        Request::HEADER_X_FORWARDED_HOST |
                        Request::HEADER_X_FORWARDED_PORT |
                        Request::HEADER_X_FORWARDED_PROTO
            );
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
