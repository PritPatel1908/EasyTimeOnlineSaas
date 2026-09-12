<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $domainStatus = tenancy()->central(function () use ($request): ?string {
            return DB::table('domains')
                ->where('domain', $request->getHost())
                ->value('status');
        });

        if ($domainStatus !== 'Active') {
            return response()->view('errors.domain-inactive', [
                'domainStatus' => $domainStatus,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
