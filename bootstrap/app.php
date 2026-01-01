<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: ['file' => __DIR__.'/../routes/api.php', 'prefix' => 'api'],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //$middleware->prepend(\App\Http\Middleware\OwnCors::class);
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\HasRole::class,
            'permission' => \App\Http\Middleware\HasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();
