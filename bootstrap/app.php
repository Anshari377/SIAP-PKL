<?php

use App\Http\Middleware\EnsureAgencyAdmin;
use App\Http\Middleware\EnsureOnboarded;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTipeKelompok;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'ensure.onboarded' => EnsureOnboarded::class,
            'ensure.kelompok' => EnsureTipeKelompok::class,
            'ensure.agency_admin' => EnsureAgencyAdmin::class,
            'ensure.super_admin' => EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return $response;
            }

            if ($status === 404 || $status === 403) {
                return Inertia::render(
                    "Errors/{$status}",
                    ['status' => $status],
                    $status
                );
            }

            return $response;
        });
    })->create();
