<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\TenantPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TenantModulePermission
{
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        TenantPermissions::authorize($module, $action);

        return $next($request);
    }
}
