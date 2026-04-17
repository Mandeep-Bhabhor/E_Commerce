<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/frontend.php',
        ],
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Sanctum support for API auth
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Redirect guests for web routes
        $middleware->redirectGuestsTo(function ($request) {
            session()->flash('error', '⚠️ Please login first to access this page.');
            return route('login');
        });

        // Custom middleware aliases
        $middleware->alias([
            'admin'    => \App\Http\Middleware\EnsureIsAdmin::class,
            'customer' => \App\Http\Middleware\EnsureIsCustomer::class,
            '2fa'      => \App\Http\Middleware\Ensure2FAIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (AuthenticationException $e, $request) {

            // Only customize API responses
            if ($request->expectsJson() && $request->bearerToken()) {

                $plainToken = $request->bearerToken();
                $tokenParts = explode('|', $plainToken);

                if (count($tokenParts) === 2) {

                    $tokenId = $tokenParts[0];
                    $accessToken = PersonalAccessToken::find($tokenId);

                    if ($accessToken) {

                        // Explicit expires_at check
                        if (
                            $accessToken->expires_at &&
                            Carbon::now()->greaterThan($accessToken->expires_at)
                        ) {
                            return response()->json([
                                'message' => 'Token expired'
                            ], 401);
                        }

                        // Sanctum global expiration fallback
                        $expiration = config('sanctum.expiration');

                        if (
                            $expiration &&
                            Carbon::parse($accessToken->created_at)
                                ->addMinutes($expiration)
                                ->isPast()
                        ) {
                            return response()->json([
                                'message' => 'Token expired'
                            ], 401);
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        });
    })
    ->create();