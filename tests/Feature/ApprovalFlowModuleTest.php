<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ApprovalFlowController;
use App\Jobs\Tenant\ProcessApprovalFlowImport;
use App\Models\Tenant\ApprovalFlow;
use App\Support\CsvImportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ApprovalFlowModuleTest extends TestCase
{
    public function test_approval_flow_routes_exist(): void
    {
        $this->assertTrue(Route::has('tenant.employee-structure.approval-flows.index'));
        $this->assertTrue(Route::has('tenant.employee-structure.approval-flows.filter'));
        $this->assertTrue(Route::has('tenant.employee-structure.approval-flows.import'));
        $this->assertTrue(Route::has('tenant.employee-structure.approval-flows.export'));
        $this->assertTrue(Route::has('tenant.employee-structure.approval-flows.download-export'));
        $this->assertContains(
            'approvalFlow',
            Route::getRoutes()->getByName('tenant.employee-structure.approval-flows.edit')->parameterNames(),
        );
    }

    public function test_resource_actions_resolve_ids_from_named_route_parameters(): void
    {
        foreach (['show', 'edit', 'update', 'destroy'] as $action) {
            $parameters = (new \ReflectionMethod(ApprovalFlowController::class, $action))->getParameters();
            $this->assertCount(1, $parameters, $action);
            $this->assertSame(Request::class, (string) $parameters[0]->getType(), $action);
        }
    }

    public function test_approval_flow_import_template_has_all_columns_and_hints(): void
    {
        $response = app(ApprovalFlowController::class)->downloadImportSample();
        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ApprovalFlow::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(2, $rows);
        $this->assertCount(count(ApprovalFlow::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertStringContainsString('email addresses or user_code values', $rows[1][2]);
    }

    public function test_approval_flow_form_renders_code_and_approver_fields_without_manual_level_input(): void
    {
        view()->share('errors', new ViewErrorBag());
        $flow = new ApprovalFlow();
        $flow->approval_flow_code = 'test-flow';
        $flow->status = 0;
        $employee = (object) [
            'id' => 7,
            'name' => 'Test Employee',
            'email' => 'employee@example.test',
            'code' => 'EMP007',
        ];

        $html = view('employee-structure.approval-flows.form', [
            'approvalFlow' => $flow,
            'users' => collect(),
            'employees' => collect([$employee]),
            'details' => [['level' => 1, 'users' => []]],
            'approvalModules' => [
                'leave_approval_flow_id' => 'Leave',
                'manual_punch_approval_flow_id' => 'Manual Punch',
            ],
            'employeeAssignments' => [
                'leave_approval_flow_id' => [7],
                'manual_punch_approval_flow_id' => [],
            ],
        ])->render();

        $this->assertStringContainsString('name="approval_flow_code"', $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('>Active</option>', $html);
        $this->assertStringContainsString('<option value="0" selected>Inactive</option>', $html);
        $this->assertStringContainsString('name="details[0][users][]"', $html);
        $this->assertStringContainsString('name="employee_assignments[leave_approval_flow_id][]"', $html);
        $this->assertStringContainsString('name="employee_assignments[manual_punch_approval_flow_id][]"', $html);
        $this->assertStringContainsString('<option value="7" selected>Test Employee', $html);
        $this->assertStringNotContainsString('name="details[0][level]"', $html);
        $this->assertStringNotContainsString('id="approval_level_0"', $html);
    }

    public function test_approval_flow_import_accepts_multiple_levels_and_approver_groups_in_one_row(): void
    {
        $job = new ProcessApprovalFlowImport(null, 'unused.csv');
        $method = new \ReflectionMethod($job, 'parseLevelAssignments');
        $method->setAccessible(true);

        $this->assertSame([
            ['level' => 1, 'users' => ['1067', 'superadmin']],
            ['level' => 2, 'users' => ['1067']],
        ], $method->invoke($job, '1,2', '1067|superadmin,1067'));
        $this->assertSame([
            ['level' => 1, 'users' => ['admin@example.test', 'EMP007']],
        ], $method->invoke($job, '1', 'admin@example.test|EMP007'));
        $this->assertNull($method->invoke($job, '1,2', 'admin@example.test'));
    }
}
