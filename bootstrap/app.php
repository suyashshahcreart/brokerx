<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\RewriteImagePaths;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply after the standard web middleware so rendered HTML is rewritten
        $middleware->appendToGroup('web', RewriteImagePaths::class);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'not.customer' => \App\Http\Middleware\BlockCustomerRole::class,
            'verify.tour.token' => \App\Http\Middleware\VerifyTourToken::class,
        ]);

        // Exclude API routes from CSRF verification for Postman testing
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Redirect unauthenticated users to login (conditional based on route)
        $middleware->redirectGuestsTo(function ($request) {
            // If accessing admin routes, redirect to admin login
            if ($request->is('admin') || $request->is('admin/*')) {
                return '/ppadmlog/login';
            }
            // Otherwise redirect to frontend login
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Ensure API routes always return JSON
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Custom handling for unauthenticated API requests
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'code' => 'UNAUTHENTICATED'
                ], 401);
            }
        });
    })->create();
