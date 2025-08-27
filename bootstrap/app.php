<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) { // Vous pouvez enlever le ": void" si vous voulez
        
        // C'EST LA SEULE LIGNE À AJOUTER ICI
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdminMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) { // Vous pouvez enlever le ": void" ici aussi
        //
    })->create();