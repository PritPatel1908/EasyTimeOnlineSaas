<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureActiveDomain;
use App\Http\Middleware\EnsureValidTenantLicense;
use App\Models\Tenant\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Tests\TestCase;

class TenantAuthGuardTest extends TestCase
{
    public function test_tenant_auth_guard_is_configured_for_tenant_users(): void
    {
        $this->assertNotNull(config('auth.guards.tenant'));
        $this->assertSame('tenant_users', config('auth.guards.tenant.provider'));
        $this->assertSame('tenant', (new User)->getDefaultGuardName());
    }

    public function test_tenant_initialization_runs_after_session_and_before_authentication(): void
    {
        $route = app('router')->getRoutes()->getByName('tenant.dashboard');
        $this->assertNotNull($route);

        $middleware = app('router')->gatherRouteMiddleware($route);
        $initializationIndex = array_search(InitializeTenancyByDomain::class, $middleware, true);
        $sessionIndex = array_search(StartSession::class, $middleware, true);
        $bindingsIndex = array_search(SubstituteBindings::class, $middleware, true);
        $centralDomainIndex = array_search(
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            $middleware,
            true,
        );
        $activeDomainIndex = array_search(EnsureActiveDomain::class, $middleware, true);
        $licenseIndex = array_search(EnsureValidTenantLicense::class, $middleware, true);
        $authenticationIndex = null;

        foreach ($middleware as $index => $entry) {
            if (str_starts_with($entry, Authenticate::class.':')) {
                $authenticationIndex = $index;

                break;
            }
        }

        $this->assertIsInt($initializationIndex);
        $this->assertIsInt($sessionIndex);
        $this->assertIsInt($bindingsIndex);
        $this->assertIsInt($centralDomainIndex);
        $this->assertIsInt($activeDomainIndex);
        $this->assertIsInt($licenseIndex);
        $this->assertIsInt($authenticationIndex);
        $this->assertLessThan($initializationIndex, $sessionIndex);
        $this->assertLessThan($centralDomainIndex, $initializationIndex);
        $this->assertLessThan($activeDomainIndex, $centralDomainIndex);
        $this->assertLessThan($licenseIndex, $activeDomainIndex);
        $this->assertLessThan($bindingsIndex, $initializationIndex);
        $this->assertLessThan($authenticationIndex, $licenseIndex);
    }
}
