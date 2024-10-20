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
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('model:prune')->daily();
        $schedule->command('telescope:purge --hours=72')->daily();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens([
            '/api/call',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
