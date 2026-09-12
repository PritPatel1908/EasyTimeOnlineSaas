<?php

namespace Tests\Feature;

use App\Models\Tenant\User;
use Tests\TestCase;

class TenantAuthGuardTest extends TestCase
{
    public function test_tenant_auth_guard_is_configured_for_tenant_users(): void
    {
        $this->assertNotNull(config('auth.guards.tenant'));
        $this->assertSame('tenant_users', config('auth.guards.tenant.provider'));
        $this->assertSame('tenant', (new User)->getDefaultGuardName());
    }
}
