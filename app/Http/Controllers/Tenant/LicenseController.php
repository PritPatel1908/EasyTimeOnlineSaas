<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\CompanyLicense;
use App\Models\Tenant\License;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class LicenseController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $licenseKey = (string) $request->validate([
            'license_key' => ['required', 'string', 'max:10000'],
        ])['license_key'];

        $companyId = tenant()?->company_id;

        $centralLicense = tenancy()->central(function () use ($licenseKey, $companyId): ?CompanyLicense {
            $license = CompanyLicense::query()
                ->where('company_id', $companyId)
                ->latest('id')
                ->first();

            if ($license === null || ! hash_equals((string) $license->license_key, $licenseKey)) {
                return null;
            }

            return $license;
        });

        $payload = $centralLicense?->keyPayload();
        $error = $centralLicense === null
            ? 'This licence key is not valid for this tenant.'
            : (($payload === null || ($payload['expiry_date'] ?? null) < now()->toDateString())
                ? 'This licence key is expired or invalid.'
                : null);

        if ($error !== null) {
            return back()->withErrors(['license_key' => $error]);
        }

        try {
            $details = Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (Throwable) {
            return back()->withErrors(['license_key' => 'The licence key could not be processed.']);
        }

        License::query()->updateOrCreate([], [
            'license_hash' => hash('sha256', $licenseKey),
            'license_details' => $details,
        ]);

        return back()->with('license_success', 'Licence updated successfully.');
    }
}
