<?php

namespace Tests\Feature;

use App\Models\Tenant\Category;
use App\Models\Tenant\Designation;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\User;
use App\Support\TenantPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EmployeeStructureRouteTest extends TestCase
{
    public function test_employee_structure_category_route_exists(): void
    {
        $this->assertTrue(Route::has('tenant.employee-structure.categories.index'));
    }

    public function test_employee_structure_designation_routes_exist(): void
    {
        $this->assertTrue(Route::has('tenant.employee-structure.designations.index'));
        $this->assertTrue(Route::has('tenant.employee-structure.designations.import'));
        $this->assertTrue(Route::has('tenant.employee-structure.designations.export'));
    }

    public function test_super_admin_user_id_1_has_no_employee_structure_permission_restriction(): void
    {
        $user = new User();
        $user->id = 1;

        Auth::guard('tenant')->setUser($user);

        $this->assertTrue(TenantPermissions::userCan('Category', 'read'));
        $this->assertTrue(TenantPermissions::userCan('Category', 'create'));
        $this->assertTrue(TenantPermissions::userCan('Designation', 'read'));
        $this->assertTrue(TenantPermissions::userCan('Designation', 'create'));
    }

    public function test_category_form_views_render_flash_alerts(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        session(['success' => 'Category saved successfully.']);

        $category = new Category();
        $category->id = 1;
        $category->name = 'Test Category';
        $category->setRelation('c_off_against_wo_hl_slabs', collect());
        $category->setRelation('c_off_against_ot_slabs', collect());

        $addView = view('employee-structure.categories.add', [
            'category' => null,
            'companies' => collect(),
            'locations' => collect(),
            'leaveTypes' => collect(),
        ])->render();

        $editView = view('employee-structure.categories.edit', [
            'category' => $category,
            'companies' => collect(),
            'locations' => collect(),
            'leaveTypes' => collect(),
        ])->render();

        $this->assertStringContainsString('auto-dismiss-alert', $addView);
        $this->assertStringContainsString('alert-success', $editView);
    }

    public function test_designation_form_views_render_flash_alerts(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        session(['success' => 'Designation saved successfully.']);

        $designation = new Designation();
        $designation->id = 1;
        $designation->name = 'Test Designation';

        $addView = view('employee-structure.designations.add', [
            'designation' => null,
            'companies' => collect(),
            'locations' => collect(),
            'categories' => collect(),
        ])->render();

        $editView = view('employee-structure.designations.edit', [
            'designation' => $designation,
            'companies' => collect(),
            'locations' => collect(),
            'categories' => collect(),
        ])->render();

        $this->assertStringContainsString('Designation Name', $addView);
        $this->assertStringContainsString('alert-success', $editView);
    }

    public function test_monthly_leave_group_edit_form_renders_existing_monthly_rules(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $leaveGroup = new LeaveGroup();
        $leaveGroup->id = 1;
        $leaveGroup->name = 'Monthly Group';
        $leaveGroup->is_monthly = true;
        $leaveGroup->status = 1;

        $detail = new GradeWiseMonthlyLeaveDetail();
        $detail->id = 17;
        $detail->increment = 2;
        $detail->proportionate = 1;
        $detail->count_weekly_off_as_present = true;
        $detail->count_holiday_as_present = true;
        $detail->start_credit = 1;
        $detail->credit_after_days = 0;
        $detail->start_use_based_on = 1;
        $detail->start_use_after_days = 6;
        $detail->allow_sandwich = true;
        $detail->sandwich_leave_id = 6;
        $detail->max_accumulation = 36;
        $detail->is_paid = true;
        $detail->keeps_balance = true;
        $detail->eligible_after_days = 0;
        $detail->credit_after_days = 5;
        $detail->min_avail = 0;
        $detail->max_avail = 365;
        $detail->leave_types_not_to_be_clubbed = [6];
        $detail->leave_credit_type = 'monthly';
        $detail->allow_negative_balance = true;
        $detail->negative_balance = -1;
        $detail->allow_backdate_leave = true;
        $detail->backdate_day_limit = 2;
        $detail->allow_advance_leave = true;
        $detail->advance_day_limit = 4;
        $detail->allow_leave_encashment = true;
        $detail->allow_leave_lapse = false;
        $detail->setRelation('leave_type', new LeaveType(['description' => 'Annual Leave']));
        $leaveGroup->setRelation('grade_wise_monthly_leave_details', collect([$detail]));
        $leaveGroup->setRelation('grade_wise_yearly_leave_details', collect());
        $leaveGroup->financial_year_id = null;

        $html = view('employee-structure.leave-groups.edit', [
            'leaveGroup' => $leaveGroup,
            'financialYears' => collect(),
            'leaveTypes' => collect([
                new LeaveType(['id' => 3, 'code' => 'AL', 'description' => 'Annual Leave']),
                new LeaveType(['id' => 6, 'code' => 'SL', 'description' => 'Sick Leave']),
            ]),
        ])->render();

        $this->assertStringContainsString('Annual Leave', $html);
        $this->assertStringContainsString('name="monthly_rules[0][id]" value="17"', $html);
        $this->assertStringContainsString('name="monthly_rules[0][leave_type_id]"', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[credit_after_days\]"\s+value="5"/', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[max_accumulation\]"\s+value="36"/', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[maximum_request_in_a_month\]"\s+value="0"/', $html);
        $this->assertStringContainsString('name="monthly_rules[0][is_paid]"', $html);
        $this->assertStringContainsString('Paid?', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[is_paid\]"[^>]*type="checkbox"[^>]*checked/', $html);
        $this->assertStringContainsString('Keeps Balance?', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[keeps_balance\]"[^>]*type="checkbox"[^>]*checked/', $html);
        $this->assertStringContainsString('Maximum Accumulation', $html);
        $this->assertStringContainsString('Maximum Request in a Month', $html);
        $this->assertStringContainsString('Maximum Request in a Year', $html);
        $this->assertSame(6, substr_count($html, '0 for unrestricted'));
        $this->assertStringContainsString('Eligible Leave After Days', $html);
        $this->assertStringContainsString('Credit Leave After Days', $html);
        $this->assertStringContainsString('Min Avail', $html);
        $this->assertStringContainsString('Max Avail', $html);
        $this->assertStringContainsString('365 for unrestricted', $html);
        $this->assertStringContainsString('name="monthly_rules[0][leave_types_not_to_be_clubbed][]"', $html);
        $this->assertStringContainsString('name="monthly_rules[0][leave_credit_type]"', $html);
        $this->assertStringContainsString('Based On Working Hrs', $html);
        $this->assertStringContainsString('Yearly Leave Rules', $html);
        $this->assertStringContainsString('name="monthly_rules[0][allow_leave_encashment]"', $html);
        $this->assertStringContainsString('Allow Leave Lapse?', $html);
        $this->assertMatchesRegularExpression('/name="monthly_rules\[0\]\[allow_leave_lapse\]"[^>]*type="checkbox"[^>]*value="1"/', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][increment]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][proportionate]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][start_credit]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][start_use_based_on]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][start_use_after_days]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][allow_sandwich]"', $html);
        $this->assertStringNotContainsString('name="monthly_rules[0][sandwich_leave_id]"', $html);
        foreach ([
            'leave_type_id', 'allow_c_f',
            'count_weekly_off_as_present', 'count_holiday_as_present',
            'credit_after_days', 'max_accumulation', 'is_paid',
            'keeps_balance',
            'allow_negative_balance', 'negative_balance', 'allow_backdate_leave',
            'backdate_day_limit', 'allow_advance_leave', 'advance_day_limit',
            'allow_leave_encashment', 'allow_leave_lapse',
            'maximum_request_in_a_month', 'maximum_request_in_a_year',
            'eligible_after_days', 'credit_after_days', 'min_avail', 'max_avail',
            'leave_types_not_to_be_clubbed', 'leave_credit_type',
        ] as $field) {
            $suffix = $field === 'leave_types_not_to_be_clubbed' ? '][]' : ']';
            $this->assertStringContainsString('name="monthly_rules[0]['.$field.$suffix.'"', $html);
        }
    }

    public function test_yearly_leave_group_edit_form_handles_missing_rules_and_renders_yearly_values(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $leaveGroup = new LeaveGroup();
        $leaveGroup->id = 2;
        $leaveGroup->name = 'Yearly Group';
        $leaveGroup->is_monthly = false;
        $leaveGroup->status = 1;

        $detail = new GradeWiseYearlyLeaveDetail();
        $detail->id = 24;
        $detail->eligible = 15;
        $detail->min_utilization = 3;
        $detail->max_accumulation = 60;
        $detail->opening = 2;
        $detail->based_on = 1;
        $detail->allow_sandwich = true;
        $detail->allow_negative_balance = true;
        $detail->negative_balance = -2;
        $detail->allow_leave_encashment = true;
        $detail->allow_leave_lapse = true;
        $detail->is_paid = false;
        $detail->keeps_balance = false;
        $detail->eligible_after_days = 0;
        $detail->credit_after_days = 0;
        $detail->min_avail = 0;
        $detail->max_avail = 365;
        $detail->leave_types_not_to_be_clubbed = [];
        $detail->leave_credit_type = 'yearly';
        $detail->setRelation('leave_type', new LeaveType(['description' => 'Sick Leave']));
        $leaveGroup->setRelation('grade_wise_monthly_leave_details', collect());
        $leaveGroup->setRelation('grade_wise_yearly_leave_details', collect([$detail]));

        $html = view('employee-structure.leave-groups.edit', [
            'leaveGroup' => $leaveGroup,
            'financialYears' => collect(),
            'leaveTypes' => collect([
                new LeaveType(['id' => 3, 'code' => 'AL', 'description' => 'Annual Leave']),
                new LeaveType(['id' => 6, 'code' => 'SL', 'description' => 'Sick Leave']),
            ]),
        ])->render();

        $this->assertStringContainsString('Sick Leave', $html);
        $this->assertStringContainsString('name="yearly_rules[0][id]" value="24"', $html);
        $this->assertStringContainsString('name="yearly_rules[0][leave_type_id]"', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[eligible\]"\s+value="15"/', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[min_utilization\]"\s+value="3"/', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[opening\]"\s+value="2"/', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[based_on\]"\s+value="1"/', $html);
        $this->assertStringContainsString('name="yearly_rules[0][allow_sandwich]"', $html);
        $this->assertStringContainsString('name="yearly_rules[0][allow_leave_encashment]"', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[max_accumulation\]"\s+value="60"/', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[maximum_request_in_a_month\]"\s+value="0"/', $html);
        $this->assertStringContainsString('name="yearly_rules[0][is_paid]"', $html);
        $this->assertStringContainsString('Paid?', $html);
        $this->assertStringContainsString('Keeps Balance?', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[is_paid\]"[^>]*type="checkbox"(?![^>]*checked)[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/name="yearly_rules\[0\]\[keeps_balance\]"[^>]*type="checkbox"(?![^>]*checked)[^>]*>/', $html);
        $this->assertStringContainsString('Maximum Accumulation', $html);
        $this->assertStringContainsString('Maximum Request in a Month', $html);
        $this->assertStringContainsString('Maximum Request in a Year', $html);
        $this->assertStringContainsString('Eligible Leave After Days', $html);
        $this->assertStringContainsString('Credit Leave After Days', $html);
        $this->assertStringContainsString('Min Avail', $html);
        $this->assertStringContainsString('Max Avail', $html);
        $this->assertStringContainsString('name="yearly_rules[0][leave_types_not_to_be_clubbed][]"', $html);
        $this->assertStringContainsString('name="yearly_rules[0][leave_credit_type]"', $html);
        $this->assertStringContainsString('Monthly Leave Rules', $html);
        foreach ([
            'leave_type_id', 'eligible', 'allow_c_f', 'opening', 'based_on',
            'allow_sandwich', 'sandwich_leave_id', 'min_utilization',
            'max_accumulation', 'maximum_request_in_a_month', 'maximum_request_in_a_year',
            'is_paid', 'keeps_balance', 'allow_negative_balance',
            'negative_balance', 'eligible_after_days', 'credit_after_days', 'min_avail',
            'max_avail', 'leave_types_not_to_be_clubbed', 'leave_credit_type',
            'allow_leave_encashment', 'allow_leave_lapse',
        ] as $field) {
            $suffix = $field === 'leave_types_not_to_be_clubbed' ? '][]' : ']';
            $this->assertStringContainsString('name="yearly_rules[0]['.$field.$suffix.'"', $html);
        }

        $leaveGroup->setRelation('grade_wise_yearly_leave_details', collect());
        $emptyHtml = view('employee-structure.leave-groups.edit', [
            'leaveGroup' => $leaveGroup,
            'financialYears' => collect(),
            'leaveTypes' => collect(),
        ])->render();

        $this->assertStringContainsString('No yearly leave rules are linked to this group.', $emptyHtml);
    }
}
