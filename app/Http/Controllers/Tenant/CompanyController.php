<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCompanyRequest;
use App\Http\Requests\Tenant\UpdateCompanyRequest;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::query()
            ->with('location')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.index', [
            'companies' => $companies,
            'locations' => $locations,
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $query = Company::query()
            ->with('location')
            ->latest('id');

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', (int) $validated['status']);
        }

        $companies = $query->get();

        return response()->json([
            'html' => view('company-structure.companies.partials.rows', [
                'companies' => $companies,
            ])->render(),
            'count' => $companies->count(),
        ]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::query()->create($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company created successfully.');
    }

    public function create(): View
    {
        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.add', [
            'locations' => $locations,
        ]);
    }

    public function edit(Request $request): View
    {
        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.edit', [
            'company' => Company::query()->findOrFail($request->route('company')),
            'locations' => $locations,
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        Company::query()->findOrFail($request->route('company'))->update($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company updated successfully.');
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:1,0'],
        ]);

        $companyRecord = Company::query()->findOrFail($request->route('company'));
        $companyRecord->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Company status updated successfully.',
            'status' => $companyRecord->status,
            'statusLabel' => $companyRecord->status === 1 ? 'Active' : 'Inactive',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $company = Company::query()->findOrFail($request->route('company'));

        if ($company->hasRelatedRecords()) {
            return redirect(url('company-structure/companies'))
                ->with('error', 'This company cannot be deleted because it is used in other records.');
        }

        $company->delete();

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company deleted successfully.');
    }
}
