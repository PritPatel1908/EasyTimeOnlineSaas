<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Central\CreateTenantAndDomains;
use App\Jobs\Central\MigrateTenantDatabase;
use App\Jobs\Central\SeedTenantData;
use App\Models\Central\Company;
use App\Models\Central\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTenantTest extends TestCase
{
    use RefreshDatabase;

    private const TENANT_ENDPOINT = 'http://admin.saas.test/tenants';

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.database.prefix' => 'tenants/test-'.str_replace('.', '', uniqid('', true)).'-']);
        $this->actingAs(User::factory()->create());
        Company::query()->create(['name' => 'Acme Incorporated', 'status' => 'Active']);
    }

    public function test_create_tenant_form_uses_admin_layout_and_components(): void
    {
        $response = $this->get('http://admin.saas.test/tenants/create');

        $response->assertSuccessful()
            ->assertSee('name="company_id"', false)
            ->assertSee('name="slug"', false)
            ->assertSee('Tenant details')
            ->assertSee('Database connection')
            ->assertSee('Database credentials')
            ->assertSee('Create Tenant')
            ->assertDontSee('Initial administrator')
            ->assertDontSee('name="admin_name"', false)
            ->assertDontSee('name="admin_email"', false)
            ->assertDontSee('name="admin_password"', false)
            ->assertDontSee('name="admin_password_confirmation"', false)
            ->assertDontSee('Create Client');
    }

    public function test_tenant_creation_dispatches_provisioning_chain(): void
    {
        Bus::fake();

        $response = $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());

        $response->assertRedirect('/tenants');
        Bus::assertChained([
            CreateTenantAndDomains::class,
            MigrateTenantDatabase::class,
            SeedTenantData::class,
        ]);
    }

    public function test_tenant_creation_persists_the_tenant_before_redirecting(): void
    {
        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());

        $this->assertDatabaseHas('tenants', [
            'id' => 'acme',
        ]);

        $data = json_decode((string) DB::table('tenants')->where('id', 'acme')->value('data'), true);

        $this->assertSame('sqlite', $data['tenancy_db_connection']);
        $this->assertSame('127.0.0.1', $data['tenancy_db_host']);
        $this->assertSame('test', $data['tenancy_db_username']);
    }

    public function test_slug_must_be_lowercase_url_safe_and_unique(): void
    {
        $this->post(self::TENANT_ENDPOINT, [
            'company_id' => Company::query()->value('id'),
            'slug' => 'Acme Inc',
        ])->assertSessionHasErrors('slug');

        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());

        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload([
            'company_name' => 'Another Acme',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_listing_shows_created_tenants(): void
    {
        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());

        $this->get('http://admin.saas.test/tenants')
            ->assertSuccessful()
            ->assertSee('Acme Incorporated')
            ->assertSee('acme')
            ->assertSee('2')
            ->assertSee('Actions')
            ->assertSee('Migrate')
            ->assertSee('Seed')
            ->assertSee(route('admin.tenants.edit', 'acme'), false)
            ->assertSee(route('admin.tenants.destroy', 'acme'), false);
    }

    public function test_migrate_action_dispatches_tenant_migration_job(): void
    {
        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());
        Bus::fake();

        $this->post(route('admin.tenants.migrate', 'acme'))
            ->assertRedirect(route('admin.tenants.index'));

        Bus::assertDispatched(MigrateTenantDatabase::class, fn (MigrateTenantDatabase $job): bool => $job->tenantId === 'acme');
    }

    public function test_seed_action_dispatches_tenant_seeder_job(): void
    {
        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());
        Bus::fake();

        $this->post(route('admin.tenants.seed', 'acme'))
            ->assertRedirect(route('admin.tenants.index'));

        Bus::assertDispatched(SeedTenantData::class, fn (SeedTenantData $job): bool => $job->tenantId === 'acme');
    }

    public function test_edit_form_is_prefilled_with_tenant_details(): void
    {
        $this->post(self::TENANT_ENDPOINT, $this->tenantPayload());

        $this->get(route('admin.tenants.edit', 'acme'))
            ->assertSuccessful()
            ->assertSee('Acme Incorporated')
            ->assertSee('value="acme"', false)
            ->assertDontSee('name="admin_password"', false)
            ->assertDontSee('name="admin_password_confirmation"', false);
    }

    /** @param array<string, string> $overrides */
    private function tenantPayload(array $overrides = []): array
    {
        return array_merge([
            'company_id' => (string) Company::query()->value('id'),
            'slug' => 'acme',
            'db_connection' => 'sqlite',
            'db_host' => '127.0.0.1',
            'db_port' => '1',
            'db_database' => 'tenant-'.str_replace('.', '', uniqid('', true)),
            'db_username' => 'test',
            'db_password' => '',
        ], $overrides);
    }
}
