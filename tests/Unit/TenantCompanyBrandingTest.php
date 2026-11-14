<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\TenantCompanyBranding;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantCompanyBrandingTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        config([
            'database.connections.tenant_company_branding_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'tenant_company_branding_test',
        ]);
        DB::purge('tenant_company_branding_test');
    }

    protected function tearDown(): void
    {
        DB::purge('tenant_company_branding_test');
        config(['database.default' => $this->originalDefaultConnection]);
        config()->offsetUnset('database.connections.tenant_company_branding_test');

        parent::tearDown();
    }

    public function test_image_urls_are_empty_when_general_configuration_table_is_missing(): void
    {
        $imageUrls = app(TenantCompanyBranding::class)->imageUrls('tenant-1');

        $this->assertSame(array_fill_keys(TenantCompanyBranding::IMAGE_KEYS, null), $imageUrls);
    }
}
