<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/frontend.php', ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // then: function () {
        //     require base_path('routes/frontend.php');
        // }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->redirectGuestsTo(function ($request) {

            session()->flash('error', '⚠️ Please login first to access this page.');

            return route('login');
        });

        $middleware->alias([
            // 'isAdmin' => AdminMiddleware::class,
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'customer' => \App\Http\Middleware\EnsureIsCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
