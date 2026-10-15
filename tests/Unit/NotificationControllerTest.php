<?php

namespace Tests\Unit;

use App\Http\Controllers\Tenant\NotificationController;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function test_it_reads_preview_for_canteen_facility_export(): void
    {
        Storage::fake('local');

        $fileName = 'canteen_facilities_20240601_120000.csv';
        $path = 'canteen-facility-exports/' . $fileName;
        Storage::disk('local')->put($path, "id,name\n1,Alpha\n2,Beta\n");

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/storage/' . $path);

        $this->assertNull($error);
        $this->assertSame(['id', 'name'], $preview['headers']);
        $this->assertCount(2, $preview['rows']);
    }

    public function test_it_reads_preview_for_category_export(): void
    {
        Storage::fake('local');

        $fileName = 'categories_20240601_120000.csv';
        $path = 'category-exports/' . $fileName;
        Storage::disk('local')->put($path, "name,code,status\nGeneral,GEN,Active\n");

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/storage/' . $path);

        $this->assertNull($error);
        $this->assertSame(['name', 'code', 'status'], $preview['headers']);
        $this->assertCount(1, $preview['rows']);
    }

    public function test_it_reads_preview_for_employee_export(): void
    {
        Storage::fake('local');

        $fileName = 'employees_20240601_120000.csv';
        $path = 'employee-exports/' . $fileName;
        Storage::disk('local')->put($path, "code,name,email\nE001,Alex,alex@example.test\n");

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/employees/export/download/' . $fileName);

        $this->assertNull($error);
        $this->assertSame(['code', 'name', 'email'], $preview['headers']);
        $this->assertSame([['E001', 'Alex', 'alex@example.test']], $preview['rows']);
    }

    public function test_it_reads_preview_for_approval_flow_export(): void
    {
        Storage::fake('local');

        $fileName = 'approval_flows_20240601_120000.csv';
        $path = 'approval-flow-exports/' . $fileName;
        Storage::disk('local')->put($path, "approval_flow_code,level,users\nDefault,1,admin@example.test|EMP007\n");

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/approval-flows/export/download/' . $fileName);

        $this->assertNull($error);
        $this->assertSame(['approval_flow_code', 'level', 'users'], $preview['headers']);
        $this->assertSame([['Default', '1', 'admin@example.test|EMP007']], $preview['rows']);
    }

    public function test_it_reads_preview_for_leave_group_export(): void
    {
        Storage::fake('local');

        $fileName = 'leave_groups_20261015_120000.csv';
        $path = 'leave-group-exports/' . $fileName;
        Storage::disk('local')->put(
            $path,
            "name,financial_year,financial_year_code,is_monthly,status,assigned_employee_count\nStandard,FY 2026-27,FY26-27,Yearly,Active,4\n",
        );

        $controller = new NotificationController();
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/leave-groups/export/download/' . $fileName);

        $this->assertNull($error);
        $this->assertSame(
            ['name', 'financial_year', 'financial_year_code', 'is_monthly', 'status', 'assigned_employee_count'],
            $preview['headers'],
        );
        $this->assertSame([['Standard', 'FY 2026-27', 'FY26-27', 'Yearly', 'Active', '4']], $preview['rows']);
    }
}
