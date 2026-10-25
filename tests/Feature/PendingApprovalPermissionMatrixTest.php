<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PendingApprovalPermissionMatrixTest extends TestCase
{
    public function test_pending_request_custom_permission_checkboxes_render_and_preserve_assignments(): void
    {
        URL::defaults(['tenant' => 'sample']);
        view()->share('errors', new ViewErrorBag());

        $role = new Role();
        $role->name = 'Approver';
        $role->setRelation('permissions', new Collection([
            new Permission(['name' => 'pendingrequest.read']),
            new Permission(['name' => 'pendingrequest.approve']),
        ]));

        $html = view('tenant.roles-permissions.permissions', [
            'role' => $role,
            'modules' => [],
            'actions' => ['read', 'write'],
            'customPermissions' => [
                'Pending Request' => [
                    'module' => 'pendingrequest',
                    'actions' => ['read', 'approve', 'reject'],
                ],
            ],
        ])->render();

        $this->assertStringContainsString('Custom permissions', $html);
        $this->assertStringContainsString('value="pendingrequest.read" checked', $html);
        $this->assertStringContainsString('value="pendingrequest.approve" checked', $html);
        $this->assertStringContainsString('value="pendingrequest.reject"', $html);
        $this->assertStringNotContainsString('value="pendingrequest.reject" checked', $html);
    }

    public function test_pending_request_routes_require_read_access_and_decisions_are_guarded_by_controller(): void
    {
        $this->assertContains(
            'tenant.permission:PendingRequest,read',
            Route::getRoutes()->getByName('tenant.requests.pending.index')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:PendingRequest,read',
            Route::getRoutes()->getByName('tenant.requests.pending.show')->gatherMiddleware(),
        );
        $this->assertSame(
            'App\Http\Controllers\Tenant\PendingApprovalController@decide',
            Route::getRoutes()->getByName('tenant.requests.pending.decision')->getActionName(),
        );
    }
}
