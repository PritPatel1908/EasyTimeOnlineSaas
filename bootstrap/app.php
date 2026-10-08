<?php

use App\Exceptions\DeleteBlockedException;
use App\Http\Middleware\EnsureActiveDomain;
use App\Http\Middleware\EnsureValidTenantLicense;
use App\Http\Middleware\TenantModulePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request): string => route('login', absolute: false));
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        );
        $middleware->appendToPriorityList(
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        );
        $middleware->appendToPriorityList(
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            EnsureActiveDomain::class,
        );
        $middleware->appendToPriorityList(
            EnsureActiveDomain::class,
            EnsureValidTenantLicense::class,
        );
        $middleware->alias([
            'tenant.permission' => TenantModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (DeleteBlockedException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return back()->with('error', $exception->getMessage());
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
