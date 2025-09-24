<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException; // <-- 1. Import this class
use Illuminate\Http\Request;                   // <-- 1. Import this class

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // --- THIS IS THE NEW, IMPORTANT CODE ---
        // We are telling Laravel how to handle an AuthenticationException
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // If the request is for our API...
            if ($request->is('api/*')) {
                // ...return a proper JSON error response with a 401 status code.
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        });
        // --- END OF THE NEW CODE ---
        
    })->create();