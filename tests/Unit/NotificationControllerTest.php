<?php

namespace Tests\Unit;

use App\Http\Controllers\Tenant\NotificationController;
use App\Models\Tenant\OvertimeRule;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function test_it_reads_preview_for_financial_year_export(): void
    {
        Storage::fake('local');

        $fileName = 'financial_years_20261020_025500.csv';
        $path = 'financial-year-exports/'.$fileName;
        Storage::disk('local')->put($path, "code,name\nFY26-27,Financial Year 2026-27\n");

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/financial-years/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['code', 'name'], $preview['headers']);
        $this->assertSame([['FY26-27', 'Financial Year 2026-27']], $preview['rows']);
    }

    public function test_it_reads_preview_for_shift_export(): void
    {
        Storage::fake('local');

        $fileName = 'shifts_20261020_033800.csv';
        Storage::disk('local')->put(
            'shift-exports/'.$fileName,
            "name,code,in_time,out_time\nMorning,AM,09:00:00,17:00:00\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/shifts/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['name', 'code', 'in_time', 'out_time'], $preview['headers']);
        $this->assertSame([['Morning', 'AM', '09:00:00', '17:00:00']], $preview['rows']);
    }

    public function test_it_reads_preview_for_shift_change_application_export(): void
    {
        Storage::fake('local');

        $fileName = 'shift_change_applications_20261024_115800.csv';
        Storage::disk('local')->put(
            'shift-change-application-exports/'.$fileName,
            "employee_codes,shift_type,shifts,shift_rotation_code,from_date,is_forever,to_date,reason,approval_status\nE001,rotational,,NIGHT,2026-10-29,1,,Correction,approved\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/shift-change-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame([
            'employee_codes',
            'shift_type',
            'shifts',
            'shift_rotation_code',
            'from_date',
            'is_forever',
            'to_date',
            'reason',
            'approval_status',
        ], $preview['headers']);
        $this->assertSame(
            [['E001', 'rotational', '', 'NIGHT', '2026-10-29', '1', '', 'Correction', 'approved']],
            $preview['rows'],
        );
    }

    public function test_it_reads_preview_for_week_off_change_application_export(): void
    {
        Storage::fake('local');

        $fileName = 'week_off_change_applications_20261024_123456.csv';
        Storage::disk('local')->put(
            'week-off-change-application-exports/'.$fileName,
            "employee_codes,from_date,approval_status\nE001,2026-10-24,pending\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/week-off-change-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'from_date', 'approval_status'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-24', 'pending']], $preview['rows']);
    }

    public function test_it_reads_preview_for_shift_rotation_export(): void
    {
        Storage::fake('local');

        $fileName = 'shift_rotations_20261020_123456.csv';
        Storage::disk('local')->put(
            'shift-rotation-exports/'.$fileName,
            "code,start_date,shift_code,days\nNIGHT,2026-10-01,N1,3\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/shift-rotations/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['code', 'start_date', 'shift_code', 'days'], $preview['headers']);
        $this->assertSame([['NIGHT', '2026-10-01', 'N1', '3']], $preview['rows']);
    }

    public function test_it_reads_preview_for_late_coming_rule_export(): void
    {
        Storage::fake('local');

        $fileName = 'late_coming_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'late-coming-rule-exports/'.$fileName,
            "code,description,ignore_late_coming_minutes,status\nLate10,Default,10,Active\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/late-coming-rules/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            ['code', 'description', 'ignore_late_coming_minutes', 'status'],
            $preview['headers'],
        );
        $this->assertSame([['Late10', 'Default', '10', 'Active']], $preview['rows']);
    }

    public function test_it_reads_preview_for_early_going_rule_export(): void
    {
        Storage::fake('local');

        $fileName = 'early_going_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'early-going-rule-exports/'.$fileName,
            "code,description,ignore_early_going_minutes,status\nEarly10,Default,10,Active\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/early-going-rules/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            ['code', 'description', 'ignore_early_going_minutes', 'status'],
            $preview['headers'],
        );
        $this->assertSame([['Early10', 'Default', '10', 'Active']], $preview['rows']);
    }

    public function test_it_reads_preview_for_half_day_rule_export(): void
    {
        Storage::fake('local');

        $fileName = 'half_day_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'half-day-rule-exports/'.$fileName,
            "code,description,single_punch_allowed,work_hr_less_than_minutes,late_coming_minutes,no_of_late,consecutive_late_coming,ignore_month_end_late,early_going_minutes,no_of_early,consecutive_early_going,ignore_month_end_early,status\nDefault,Sample,Yes,300,10,3,3,No,10,3,3,0,Active\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/half-day-rules/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            [
                'code',
                'description',
                'single_punch_allowed',
                'work_hr_less_than_minutes',
                'late_coming_minutes',
                'no_of_late',
                'consecutive_late_coming',
                'ignore_month_end_late',
                'early_going_minutes',
                'no_of_early',
                'consecutive_early_going',
                'ignore_month_end_early',
                'status',
            ],
            $preview['headers'],
        );
        $this->assertSame([['Default', 'Sample', 'Yes', '300', '10', '3', '3', 'No', '10', '3', '3', '0', 'Active']], $preview['rows']);
    }

    public function test_it_reads_preview_for_overtime_rule_export(): void
    {
        Storage::fake('local');

        $fileName = 'overtime_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'overtime-rule-exports/'.$fileName,
            implode(',', OvertimeRule::IMPORT_EXPORT_COLUMNS)."\nOT1,Default,00:15,00:10,Yes,5.5,10.25,Active,00:00,01:00,00:00,1.5\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/overtime-rules/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            [
                'code',
                'description',
                'ignore_early_come_minutes',
                'ignore_late_come_minutes',
                'ignore_late_going',
                'ignore_overtime',
                'ignore_late_going_overtime',
                'status',
                'slab_value_from',
                'slab_value_to',
                'slab_head_value',
                'slab_decimal_value',
            ],
            $preview['headers'],
        );
        $this->assertSame(
            [['OT1', 'Default', '00:15', '00:10', 'Yes', '5.5', '10.25', 'Active', '00:00', '01:00', '00:00', '1.5']],
            $preview['rows'],
        );
    }

    public function test_it_reads_preview_for_absent_rule_export(): void
    {
        Storage::fake('local');

        $fileName = 'absent_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'absent-rule-exports/'.$fileName,
            "code,description,work_hr_less_than_minutes,allow_single_punch,late_coming_minutes,no_of_late,consecutive_late_coming,ignore_month_end_late,early_going_minutes,no_of_early,consecutive_early_going,ignore_month_end_early,status\nDefault,Sample,300,Yes,10,3,3,No,10,3,3,No,Active\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/time-management/absent-rules/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            [
                'code',
                'description',
                'work_hr_less_than_minutes',
                'allow_single_punch',
                'late_coming_minutes',
                'no_of_late',
                'consecutive_late_coming',
                'ignore_month_end_late',
                'early_going_minutes',
                'no_of_early',
                'consecutive_early_going',
                'ignore_month_end_early',
                'status',
            ],
            $preview['headers'],
        );
        $this->assertSame([['Default', 'Sample', '300', 'Yes', '10', '3', '3', 'No', '10', '3', '3', 'No', 'Active']], $preview['rows']);
    }

    public function test_it_reads_preview_for_canteen_facility_export(): void
    {
        Storage::fake('local');

        $fileName = 'canteen_facilities_20240601_120000.csv';
        $path = 'canteen-facility-exports/'.$fileName;
        Storage::disk('local')->put($path, "id,name\n1,Alpha\n2,Beta\n");

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/storage/'.$path);

        $this->assertNull($error);
        $this->assertSame(['id', 'name'], $preview['headers']);
        $this->assertCount(2, $preview['rows']);
    }

    public function test_it_reads_preview_for_category_export(): void
    {
        Storage::fake('local');

        $fileName = 'categories_20240601_120000.csv';
        $path = 'category-exports/'.$fileName;
        Storage::disk('local')->put($path, "name,code,status\nGeneral,GEN,Active\n");

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/storage/'.$path);

        $this->assertNull($error);
        $this->assertSame(['name', 'code', 'status'], $preview['headers']);
        $this->assertCount(1, $preview['rows']);
    }

    public function test_it_reads_preview_for_employee_export(): void
    {
        Storage::fake('local');

        $fileName = 'employees_20240601_120000.csv';
        $path = 'employee-exports/'.$fileName;
        Storage::disk('local')->put($path, "code,name,email\nE001,Alex,alex@example.test\n");

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/employees/export/download/'.$fileName);

        $this->assertNull($error);
        $this->assertSame(['code', 'name', 'email'], $preview['headers']);
        $this->assertSame([['E001', 'Alex', 'alex@example.test']], $preview['rows']);
    }

    public function test_it_reads_preview_for_approval_flow_export(): void
    {
        Storage::fake('local');

        $fileName = 'approval_flows_20240601_120000.csv';
        $path = 'approval-flow-exports/'.$fileName;
        Storage::disk('local')->put($path, "approval_flow_code,level,users\nDefault,1,admin@example.test|EMP007\n");

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/approval-flows/export/download/'.$fileName);

        $this->assertNull($error);
        $this->assertSame(['approval_flow_code', 'level', 'users'], $preview['headers']);
        $this->assertSame([['Default', '1', 'admin@example.test|EMP007']], $preview['rows']);
    }

    public function test_it_reads_preview_for_leave_group_export(): void
    {
        Storage::fake('local');

        $fileName = 'leave_groups_20261015_120000.csv';
        $path = 'leave-group-exports/'.$fileName;
        Storage::disk('local')->put(
            $path,
            "name,financial_year,financial_year_code,status,assigned_employee_count\nStandard,FY 2026-27,FY26-27,Active,4\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke($controller, '/employee-structure/leave-groups/export/download/'.$fileName);

        $this->assertNull($error);
        $this->assertSame(
            ['name', 'financial_year', 'financial_year_code', 'status', 'assigned_employee_count'],
            $preview['headers'],
        );
        $this->assertSame([['Standard', 'FY 2026-27', 'FY26-27', 'Active', '4']], $preview['rows']);
    }

    public function test_it_reads_preview_for_manual_leave_balance_export(): void
    {
        Storage::fake('local');

        $fileName = 'manual_leave_balances_20261016_110000.csv';
        $path = 'manual-leave-balance-exports/'.$fileName;
        Storage::disk('local')->put(
            $path,
            "employee_codes,location,company,leave_type_code,balance,date,reason\nE001,Head Office (HO-1),Acme (AC),CL,1.5,2026-10-16 09:30:00,Adjustment\n",
        );

        $controller = new NotificationController;
        $method = new \ReflectionMethod($controller, 'readExportPreview');
        $method->setAccessible(true);

        [$preview, $error] = $method->invoke(
            $controller,
            '/employee-structure/manual-leave-balances/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            ['employee_codes', 'location', 'company', 'leave_type_code', 'balance', 'date', 'reason'],
            $preview['headers'],
        );
        $this->assertSame(
            [['E001', 'Head Office (HO-1)', 'Acme (AC)', 'CL', '1.5', '2026-10-16 09:30:00', 'Adjustment']],
            $preview['rows'],
        );
    }
}
