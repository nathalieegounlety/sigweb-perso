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
        $exceptions->reportable(function (Throwable $e) {

            // ❌ ignorer les erreurs HTTP (404, 403…)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return;
            }

            // ❌ pas d’email hors prod
            if (! app()->environment(['production', 'staging'])) {
                return;
            }

            try {
                \Illuminate\Support\Facades\Mail::to(config('mail.support.primary'))
                    ->cc(config('mail.support.cc'))
                    ->queue(new \App\Mail\SupportTechniqueError([
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => collect($e->getTrace())->take(10)->toArray(),
                        'url'     => request()->fullUrl(),
                        'method'  => request()->method(),
                        'body'    => request()->except(['password']),
                        'ip'      => request()->ip(),
                        'user_id' => auth()->id(),
                    ]));
            } catch (Throwable $mailException) {
                \Illuminate\Support\Facades\Log::error('Erreur lors de l’envoi du mail d’erreur', [
                    'exception' => $mailException,
                ]);
            }
        });
    })->create();
