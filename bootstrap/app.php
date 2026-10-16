<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'techshop.user' => \App\Http\Middleware\ResolveTechshopUser::class,
            'techshop.admin' => \App\Http\Middleware\ResolveTechshopAdmin::class,
        ]);

        // The JWT cookie is set by the legacy PHP system without Laravel encryption.
        // Exclude it so Laravel does not try to decrypt it.
        $cookieName = (string) env('TECHSHOP_JWT_COOKIE', 'techshop_token');
        $middleware->encryptCookies(except: [$cookieName]);

        // Trust all proxies so ngrok headers (X-Forwarded-*) are respected.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
