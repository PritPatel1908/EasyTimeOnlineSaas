<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\Tenant\GenerateLeaveGroupExport;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\LeaveType;
use Illuminate\Support\Collection;
use ReflectionMethod;
use Tests\TestCase;

class GenerateLeaveGroupExportTest extends TestCase
{
    public function test_export_includes_group_details_and_all_monthly_and_yearly_rule_values(): void
    {
        $annualLeave = $this->leaveType(10, 'AL', 'Annual Leave');
        $sickLeave = $this->leaveType(11, 'SL', 'Sick Leave');

        $monthlyRule = new GradeWiseMonthlyLeaveDetail();
        $monthlyRule->setRawAttributes([
            'id' => 21,
            'grade_wise_leave_id' => 5,
            'leave_type_id' => 10,
            'sandwich_leave_id' => 11,
            'increment' => 2,
            'keeps_balance' => 1,
            'allow_c_f' => 0,
            'leave_types_not_to_be_clubbed' => '[11]',
            'include_leaves' => '[10,11]',
        ], true);
        $monthlyRule->setRelation('leave_type', $annualLeave);
        $monthlyRule->setRelation('sandwich_leave', $sickLeave);

        $yearlyRule = new GradeWiseYearlyLeaveDetail();
        $yearlyRule->setRawAttributes([
            'id' => 22,
            'grade_wise_leave_id' => 5,
            'leave_type_id' => 10,
            'eligible' => 12,
            'min_utilization' => 2,
            'max_accumulation' => 30,
            'is_paid' => 1,
        ], true);
        $yearlyRule->setRelation('leave_type', $annualLeave);

        $financialYear = new FinancialYear();
        $financialYear->setRawAttributes(['id' => 3, 'name' => 'FY 2026-27', 'code' => 'FY26-27'], true);

        $leaveGroup = new LeaveGroup();
        $leaveGroup->setRawAttributes([
            'id' => 5,
            'name' => 'Annual Leave Group',
            'financial_year_id' => 3,
            'status' => 1,
        ], true);
        $leaveGroup->setAttribute('users_count', 4);
        $leaveGroup->setRelation('financial_year', $financialYear);
        $leaveGroup->setRelation('grade_wise_monthly_leave_details', collect([$monthlyRule]));
        $leaveGroup->setRelation('grade_wise_yearly_leave_details', collect([$yearlyRule]));

        $job = new GenerateLeaveGroupExport(null, 'leave_groups_test.csv');
        $buildExportCsv = new ReflectionMethod($job, 'buildExportCsv');
        $csv = $buildExportCsv->invoke(
            $job,
            new Collection([$leaveGroup]),
            new Collection([$annualLeave->id => $annualLeave, $sickLeave->id => $sickLeave]),
            new Collection(),
        );

        $handle = fopen('php://temp', 'w+');
        fwrite($handle, $csv);
        rewind($handle);
        $headers = fgetcsv($handle);
        $values = fgetcsv($handle);
        fclose($handle);

        $this->assertIsArray($headers);
        $this->assertIsArray($values);
        $row = array_combine($headers, $values);

        $this->assertSame('FY 2026-27', $row['financial_year']);
        $this->assertSame('FY26-27', $row['financial_year_code']);
        $this->assertArrayNotHasKey('is_monthly', $row);
        $this->assertSame('4', $row['assigned_employee_count']);
        $this->assertSame('AL - Annual Leave', $row['monthly_rule_1_leave_type']);
        $this->assertSame('AL', $row['monthly_rule_1_leave_type_code']);
        $this->assertSame('Annual Leave', $row['monthly_rule_1_leave_type_name']);
        $this->assertSame('SL - Sick Leave', $row['monthly_rule_1_sandwich_leave']);
        $this->assertSame('2', $row['monthly_rule_1_increment']);
        $this->assertSame('No', $row['monthly_rule_1_allow_c_f']);
        $this->assertSame('SL - Sick Leave', $row['monthly_rule_1_leave_types_not_to_be_clubbed']);
        $this->assertSame('AL - Annual Leave; SL - Sick Leave', $row['monthly_rule_1_include_leaves']);
        $this->assertSame('12', $row['yearly_rule_1_eligible']);
        $this->assertSame('Yes', $row['yearly_rule_1_is_paid']);
        $this->assertSame([], array_values(array_filter(
            $headers,
            static fn (string $header): bool => str_ends_with($header, '_id'),
        )));
    }

    private function leaveType(int $id, string $code, string $description): LeaveType
    {
        $leaveType = new LeaveType();
        $leaveType->setRawAttributes([
            'id' => $id,
            'code' => $code,
            'description' => $description,
        ], true);

        return $leaveType;
    }
}
