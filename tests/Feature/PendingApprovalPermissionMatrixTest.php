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
            new Permission(['name' => 'pendingrequest.cancel']),
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
                'Approved Request' => [
                    'module' => 'approvedrequest',
                    'actions' => ['read', 'cancel'],
                ],
                'Rejected Request' => [
                    'module' => 'rejectedrequest',
                    'actions' => ['read'],
                ],
                'Cancelled Request' => [
                    'module' => 'cancelledrequest',
                    'actions' => ['read'],
                ],
                'Upcoming Request' => [
                    'module' => 'upcomingrequest',
                    'actions' => ['read'],
                ],
            ],
        ])->render();

        $this->assertStringContainsString('Custom permissions', $html);
        preg_match_all(
            '/<table class="table permission-table custom-permission-table mb-2 align-middle">(.*?)<\/table>/s',
            $html,
            $permissionTables,
        );
        $this->assertCount(5, $permissionTables[1]);
        $expectedActions = [
            ['read', 'approve', 'reject'],
            ['read', 'cancel'],
            ['read'],
            ['read'],
            ['read'],
        ];
        foreach ($permissionTables[1] as $index => $table) {
            foreach (['read', 'approve', 'reject', 'cancel'] as $action) {
                $header = '<th class="text-center text-capitalize">'.$action.'</th>';
                if (in_array($action, $expectedActions[$index], true)) {
                    $this->assertStringContainsString($header, $table);
                } else {
                    $this->assertStringNotContainsString($header, $table);
                }
            }
        }
        $this->assertStringContainsString('Pending Request', $html);
        $this->assertStringContainsString('Approved Request', $html);
        $this->assertStringContainsString('Rejected Request', $html);
        $this->assertStringContainsString('Cancelled Request', $html);
        $this->assertStringContainsString('Upcoming Request', $html);
        $this->assertStringContainsString('value="pendingrequest.read" checked', $html);
        $this->assertStringContainsString('value="pendingrequest.approve" checked', $html);
        $this->assertStringContainsString('value="pendingrequest.reject"', $html);
        $this->assertStringNotContainsString('value="pendingrequest.reject" checked', $html);
        $this->assertStringNotContainsString('value="pendingrequest.cancel"', $html);
        $this->assertStringContainsString('value="approvedrequest.cancel" checked', $html);
        $this->assertStringNotContainsString('value="approvedrequest.approve"', $html);
        $this->assertStringNotContainsString('value="approvedrequest.reject"', $html);
        $this->assertStringContainsString('value="rejectedrequest.read"', $html);
        $this->assertStringNotContainsString('value="rejectedrequest.approve"', $html);
        $this->assertStringNotContainsString('value="rejectedrequest.cancel"', $html);
        $this->assertStringContainsString('value="cancelledrequest.read"', $html);
        $this->assertStringNotContainsString('value="cancelledrequest.cancel"', $html);
        $this->assertStringContainsString('value="upcomingrequest.read"', $html);
        $this->assertStringNotContainsString('value="upcomingrequest.cancel"', $html);
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
        $this->assertContains(
            'tenant.permission:ApprovedRequest,read',
            Route::getRoutes()->getByName('tenant.requests.approved.index')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:ApprovedRequest,read',
            Route::getRoutes()->getByName('tenant.requests.approved.show')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:ApprovedRequest,cancel',
            Route::getRoutes()->getByName('tenant.requests.approved.cancel')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:RejectedRequest,read',
            Route::getRoutes()->getByName('tenant.requests.rejected.index')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:RejectedRequest,read',
            Route::getRoutes()->getByName('tenant.requests.rejected.show')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:CancelledRequest,read',
            Route::getRoutes()->getByName('tenant.requests.cancelled.index')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:CancelledRequest,read',
            Route::getRoutes()->getByName('tenant.requests.cancelled.show')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:UpcomingRequest,read',
            Route::getRoutes()->getByName('tenant.requests.upcoming.index')->gatherMiddleware(),
        );
        $this->assertContains(
            'tenant.permission:UpcomingRequest,read',
            Route::getRoutes()->getByName('tenant.requests.upcoming.show')->gatherMiddleware(),
        );
    }
}
