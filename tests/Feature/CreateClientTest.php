<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    private const ADMIN_DASHBOARD_URL = 'http://admin.saas.test/dashboard';

    private const CLIENTS_URL = 'http://admin.saas.test/clients';

    private const COMPANY_NAME = 'Acme Labs';

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenancy.base_domains', [
            'local' => 'saas.test',
            'prod' => 'myapp.com',
        ]);
    }

    public function test_admin_can_view_the_create_client_form(): void
    {
        $this->get(self::ADMIN_DASHBOARD_URL)
            ->assertOk()
            ->assertSee('Create client')
            ->assertSee('Company name')
            ->assertSee('Subdomain slug');
    }

    public function test_admin_can_create_a_tenant_with_both_domains(): void
    {
        $slug = 'acme-'.Str::lower(Str::random(8));

        $response = $this->post(self::CLIENTS_URL, [
            'company_name' => self::COMPANY_NAME,
            'slug' => $slug,
        ]);

        $response->assertRedirect(self::ADMIN_DASHBOARD_URL)
            ->assertSessionHas('status', 'Client created successfully.');

        $tenant = Tenant::find($slug);

        $this->assertNotNull($tenant);
        $this->assertSame(self::COMPANY_NAME, $tenant->company_name);
        $this->assertDatabaseHas('domains', ['domain' => $slug.'.saas.test', 'tenant_id' => $slug]);
        $this->assertDatabaseHas('domains', ['domain' => $slug.'.myapp.com', 'tenant_id' => $slug]);
    }

    public function test_slug_must_be_url_safe_and_unique(): void
    {
        $slug = 'acme-'.Str::lower(Str::random(8));

        $this->post(self::CLIENTS_URL, [
            'company_name' => self::COMPANY_NAME,
            'slug' => $slug,
        ])->assertRedirect();

        $this->from(self::ADMIN_DASHBOARD_URL)->post(self::CLIENTS_URL, [
            'company_name' => 'Another Company',
            'slug' => 'Acme Labs',
        ])->assertRedirect(self::ADMIN_DASHBOARD_URL)
            ->assertSessionHasErrors('slug');

        /** @var TestResponse $response */
        $response = $this->from(self::ADMIN_DASHBOARD_URL)->post(self::CLIENTS_URL, [
            'company_name' => 'Another Company',
            'slug' => $slug,
        ]);

        $response->assertRedirect(self::ADMIN_DASHBOARD_URL)
            ->assertSessionHasErrors('slug');
    }
}
