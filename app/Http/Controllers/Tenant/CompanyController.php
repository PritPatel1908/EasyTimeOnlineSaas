<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCompanyRequest;
use App\Http\Requests\Tenant\UpdateCompanyRequest;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::query()->create($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company created successfully.');
    }

    public function edit(Company $company): View
    {
        $locations = Location::query()->where('status', 1)->orderBy('name')->get();

        return view('company-structure.companies.edit', [
            'company' => $company,
            'locations' => $locations,
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect(url('company-structure/companies'))
            ->with('success', 'Company deleted successfully.');
    }
}
