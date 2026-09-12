<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Company;
use App\Models\Central\CompanyLicense;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCompanyController extends Controller
{
    private const STATUSES = ['Active', 'Inactive'];

    public function index(Request $request): View
    {
        return view('admin.companies.index', ['companies' => $this->paginate($request)]);
    }

    public function pagination(Request $request): JsonResponse
    {
        return response()->json([
            'html' => view('admin.companies.partials.pagination', ['companies' => $this->paginate($request)])->render(),
        ]);
    }

    private function paginate(Request $request): mixed
    {
        $perPage = $request->validate(['per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])]])['per_page'] ?? 10;

        return Company::query()->withCount('tenants')->latest()->paginate($perPage, ['*'], 'page', $request->integer('page', 1))->withQueryString();
    }

    public function create(): View
    {
        return view('admin.companies.form', ['company' => null, 'statuses' => self::STATUSES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $license = $this->licenseData($request);
        $companyRecord = Company::query()->create($this->validated($request));

        if ($license !== null) {
            $license['license_key'] = CompanyLicense::generateKey($companyRecord, $license);
            $companyRecord->licenses()->create($license);
        }

        return redirect()->route('admin.companies.edit', $companyRecord)->with('success', 'Company and licence created successfully.');
    }

    public function edit(int $company): View
    {
        return view('admin.companies.form', [
            'company' => Company::query()->with('licenses')->findOrFail($company),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, int $company): RedirectResponse
    {
        $companyRecord = Company::query()->findOrFail($company);
        $license = $this->licenseData($request);
        $companyRecord->update($this->validated($request, $company));

        if ($license !== null && ! $this->licenseMatches($companyRecord, $companyRecord->licenses()->latest()->first(), $license)) {
            $license['license_key'] = CompanyLicense::generateKey($companyRecord, $license);
            $companyRecord->licenses()->create($license);
        }

        return redirect()->route('admin.companies.edit', $companyRecord)->with('success', 'Company updated successfully.');
    }

    public function destroy(int $company): RedirectResponse
    {
        Company::query()->findOrFail($company)->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully.');
    }

    private function validated(Request $request, ?int $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);
    }

    /** @param array<string, bool|int|string> $license */
    private function licenseMatches(Company $company, ?CompanyLicense $currentLicense, array $license): bool
    {
        if ($currentLicense === null) {
            return false;
        }

        $payload = $currentLicense->keyPayload();

        return $payload !== null
            && ($payload['company_id'] ?? null) === $company->id
            && ($payload['company_name'] ?? null) === $company->name
            && $currentLicense->have_leave === (bool) $license['have_leave']
            && $currentLicense->have_payroll === (bool) $license['have_payroll']
            && $currentLicense->location_count === (int) $license['location_count']
            && $currentLicense->company_count === (int) $license['company_count']
            && $currentLicense->user_count === (int) $license['user_count']
            && $currentLicense->expiry_date?->format('Y-m-d') === $license['expiry_date'];
    }

    /** @return array<string, bool|int|string>|null */
    private function licenseData(Request $request): ?array
    {
        if (
            ! $request->filled('expiry_date') && ! $request->boolean('have_leave') && ! $request->boolean('have_payroll')
            && $request->integer('location_count') === 0 && $request->integer('company_count') === 0 && $request->integer('user_count') === 0
        ) {
            return null;
        }

        return $request->validate([
            'have_leave' => ['required', 'boolean'],
            'have_payroll' => ['required', 'boolean'],
            'location_count' => ['required', 'integer', 'min:0'],
            'company_count' => ['required', 'integer', 'min:0'],
            'user_count' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date'],
        ], [], [
            'have_leave' => 'leave access',
            'have_payroll' => 'payroll access',
        ]);
    }
}
