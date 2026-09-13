<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Central\Company;
use App\Models\Central\CompanyLicense;
use App\Models\Central\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompanyLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_license_update_route_is_not_guarded_by_auth_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('tenant.license.update');

        $this->assertNotNull($route);
        $this->assertNotContains('auth:tenant', $route->gatherMiddleware());
    }

    public function test_company_licences_are_created_and_previous_licence_is_shown(): void
    {
        $this->actingAs(User::factory()->create());
        $company = Company::query()->create(['name' => 'Acme Labs', 'status' => 'Active']);
        $payload = [
            'name' => 'Acme Labs',
            'status' => 'Active',
            'have_leave' => '1',
            'have_payroll' => '0',
            'location_count' => '2',
            'company_count' => '1',
            'user_count' => '25',
            'expiry_date' => '2026-12-31',
        ];

        $this->put(route('admin.companies.update', $company), $payload)->assertRedirect(route('admin.companies.edit', $company));
        $this->put(route('admin.companies.update', $company), $payload)->assertRedirect(route('admin.companies.edit', $company));
        $this->put(route('admin.companies.update', $company), array_merge($payload, ['user_count' => '40', 'expiry_date' => '2027-12-31']))
            ->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseCount('company_licenses', 2);
        $this->get(route('admin.companies.edit', $company))->assertOk()->assertSee('Previous licence')->assertSee('2026-12-31');
        $this->assertSame(40, CompanyLicense::query()->latest('id')->value('user_count'));
        $this->assertNotEmpty(CompanyLicense::query()->latest('id')->value('license_key'));
        $this->assertSame('Acme Labs', CompanyLicense::query()->latest('id')->first()->keyPayload()['company_name']);
    }
}
