<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\CompanyLicense;
use App\Models\Tenant\License;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureValidTenantLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('tenant.license.update')) {
            return $next($request);
        }

        $valid = $this->hasValidLicense();
        $request->attributes->set('tenantLicenseRequired', ! $valid);

        return $next($request);
    }

    private function hasValidLicense(): bool
    {
        $storedLicense = License::query()->latest('id')->first();
        $companyId = tenant()?->company_id;

        if ($companyId === null) {
            return false;
        }

        $centralLicense = tenancy()->central(function () use ($companyId): ?CompanyLicense {
            return CompanyLicense::query()
                ->where('company_id', $companyId)
                ->latest('id')
                ->first();
        });
        $details = null;

        if ($storedLicense !== null && $centralLicense !== null && hash_equals($storedLicense->license_hash, hash('sha256', (string) $centralLicense->license_key))) {
            try {
                $details = json_decode(Crypt::decryptString($storedLicense->license_details), true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                $details = null;
            }
        }

        return is_array($details)
            && isset($details['expiry_date'])
            && now()->startOfDay()->lte($details['expiry_date']);
    }
}
