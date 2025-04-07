<?php

use App\Http\Middleware\ChangePassMiddleware;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckMod;
use App\Http\Middleware\CheckUser;
use App\Http\Middleware\PreventBackMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/auth.php',
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/user.php',
            __DIR__.'/../routes/mod.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/status',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => CheckAdmin::class,
            'user' => CheckUser::class,
            'mod' => CheckMod::class,
            'prevent-back' => PreventBackMiddleware::class,
            'change-pass' => ChangePassMiddleware::class,

        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {})->create();
